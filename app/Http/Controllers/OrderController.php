<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Shipping;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Notification;
use App\Notifications\StatusNotification;
use Helper;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = Order::orderBy('id', 'DESC')->paginate(10);
        return view('backend.order.index')->with('orders', $orders);
    }

    public function exportCsv(): StreamedResponse
    {
        $fileName = 'orders_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];
        $columns = [
            'ID', 'Order Number', 'Name', 'Email', 'Quantity', 'Payment Status',
            'Total Amount', 'Status', 'Created At'
        ];

        $callback = function () use ($columns) {
            $handle = fopen('php://output', 'w');
            // Add BOM for Excel
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, $columns);

            Order::chunk(200, function ($orders) use ($handle) {
                foreach ($orders as $order) {
                    fputcsv($handle, [
                        $order->id,
                        $order->order_number,
                        $order->first_name . ' ' . $order->last_name,
                        $order->email,
                        $order->quantity,
                        $order->payment_status,
                        number_format($order->total_amount, 2),
                        $order->status,
                        $order->created_at->toDateTimeString(),
                    ]);
                }
            });
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'status'         => 'sometimes|in:new,process,accepted,delivered,rejected',
            'payment_status' => 'sometimes|in:Unpaid,paid',
        ]);

        if (!empty($data)) {
            $order->update($data);
            return back()->with('success', 'Order updated.');
        }

        return back()->with('error', 'Nothing to update.');
    }

    public function store(Request $request)
    {
        // 1) VALIDATION
        $request->validate([
            'first_name'      => 'required|string',
            'last_name'       => 'nullable|string',
            'screenshot'      => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'address1'        => 'required|string',
            'address2'        => 'nullable|string',
            'coupon'          => 'nullable|numeric',
            'phone'           => 'required|numeric',
            'post_code'       => 'nullable|string',
            'email'           => 'nullable|email',
            'country'         => 'required|string',
            'shipping'        => 'nullable|exists:shippings,id',
            'payment_method'  => 'required|in:cod,paypal',
        ]);

        // 2) FETCH CART LINES
        $carts = Cart::where('user_id', auth()->id())
                     ->whereNull('order_id')
                     ->get();

        if ($carts->isEmpty()) {
            return back()->with('error', 'Your cart is empty!');
        }

        // 3) Calculate shipping cost
        $shippingCost = $request->shipping
            ? Shipping::find($request->shipping)->price
            : 0;

        // 4) BUILD & SAVE ORDER
        $order = new Order();
        $order->first_name      = $request->first_name;
        $order->last_name       = $request->last_name;
        $order->address1        = $request->address1;
        $order->address2        = $request->address2;
        $order->phone           = $request->phone;
        $order->post_code       = $request->post_code;
        $order->email           = $request->email;
        $order->country         = $request->country;

        if ($request->hasFile('screenshot')) {
            $order->screenshot = $request
                ->file('screenshot')
                ->store('screenshots', 'public');
        }

        $order->order_number   = 'ORD-'.Str::upper(Str::random(10));
        $order->user_id        = auth()->id();
        $order->shipping_id    = $request->shipping;
        $order->sub_total      = Helper::totalCartPrice();
        $order->quantity       = Helper::cartCount();
        $order->coupon         = session('coupon')['value'] ?? null;

        // Check if delivery_charge column exists
        if (Schema::hasColumn('orders', 'delivery_charge')) {
            $order->delivery_charge = $shippingCost;
        }

        $order->total_amount   = Helper::totalCartPrice()
                                 + $shippingCost
                                 - ($order->coupon ?: 0);

        $order->status         = 'new';
        $order->payment_method = $request->payment_method;
        $order->payment_status = $request->payment_method === 'paypal'
                                 ? 'paid'
                                 : 'Unpaid';

        $order->save();

        // 5) SAVE EACH CART LINE AS AN ORDER ITEM WITH ATTRIBUTES
        foreach ($carts as $cart) {
            // Debug: Log cart attributes before creating order item
            \Log::info('Processing cart item:', [
                'cart_id' => $cart->id,
                'product_id' => $cart->product_id,
                'cart_has_attributes' => $cart->hasSelectedAttributes(),
                'cart_attribute_options' => $cart->attribute_options,
                'cart_raw_attributes' => $cart->getRawAttributeOptions(),
            ]);

            $orderItemData = [
                'order_id' => $order->id,
                'product_id' => $cart->product_id,
                'price' => $cart->price,
                'quantity' => $cart->quantity,
                'amount' => $cart->amount,
            ];

            // Add attribute_options if cart has attributes
            if ($cart->hasSelectedAttributes() && $cart->attribute_options) {
                $orderItemData['attribute_options'] = $cart->attribute_options;
            }

            $orderItem = OrderItem::create($orderItemData);

            // Debug: Log what was actually saved
            \Log::info('Order item created:', [
                'order_item_id' => $orderItem->id,
                'saved_attributes' => $orderItem->attribute_options,
                'has_attributes' => $orderItem->hasSelectedAttributes(),
                'raw_saved_attributes' => $orderItem->getRawAttributeOptions(),
            ]);
        }

        // 6) MARK CARTS AS ORDERED
        Cart::whereIn('id', $carts->pluck('id'))
            ->update(['order_id' => $order->id]);

        // 7) NOTIFY ADMIN
        $admin = User::where('role', 'admin')->first();
        if ($admin) {
            $details = [
                'title'     => 'New order created',
                'actionURL' => route('order.show', $order->id),
                'fas'       => 'fa-file-alt',
            ];
            Notification::send($admin, new StatusNotification($details));
        }

        // 8) CLEAR SESSIONS & REDIRECT
        session()->forget(['cart', 'coupon']);
        session()->flash('success', 'Your order has been placed successfully!');

        if ($request->payment_method === 'paypal') {
            session()->flash(
                'success',
                'Thank you! Your payment screenshot has been received and is pending verification.'
            );
        }

        return redirect()->route('home');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $order = Order::with(['items.product', 'items.product.attributes.values'])->find($id);
        return view('backend.order.show')->with('order', $order);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $order = Order::find($id);
        return view('backend.order.edit')->with('order', $order);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $order = Order::find($id);
        $this->validate($request, [
            'status' => 'required|in:new,process,delivered,cancel'
        ]);

        $data = $request->all();

        // Update stock when order is delivered
        if ($request->status == 'delivered') {
            foreach ($order->items as $item) {
                $product = $item->product;
                $product->stock -= $item->quantity;
                $product->save();
            }
        }

        $status = $order->fill($data)->save();
        if ($status) {
            request()->session()->flash('success', 'Successfully updated order');
        } else {
            request()->session()->flash('error', 'Error while updating order');
        }

        return redirect()->route('order.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $order = Order::find($id);
        if ($order) {
            $status = $order->delete();
            if ($status) {
                request()->session()->flash('success', 'Order Successfully deleted');
            } else {
                request()->session()->flash('error', 'Order can not deleted');
            }
            return redirect()->route('order.index');
        } else {
            request()->session()->flash('error', 'Order can not found');
            return redirect()->back();
        }
    }

    public function orderTrack()
    {
        return view('frontend.pages.order-track');
    }

    public function productTrackOrder(Request $request)
    {
        $order = Order::where('user_id', auth()->user()->id)
                     ->where('order_number', $request->order_number)
                     ->first();

        if ($order) {
            switch ($order->status) {
                case "new":
                    request()->session()->flash('success', 'Your order has been placed. please wait.');
                    break;
                case "process":
                    request()->session()->flash('success', 'Your order is under processing please wait.');
                    break;
                case "delivered":
                    request()->session()->flash('success', 'Your order is successfully delivered.');
                    break;
                default:
                    request()->session()->flash('error', 'Your order canceled. please try again');
                    break;
            }
            return redirect()->route('home');
        } else {
            request()->session()->flash('error', 'Invalid order number please try again');
            return back();
        }
    }

    // PDF generate
    public function pdf(Request $request)
    {
        $order = Order::with(['items.product'])->find($request->id);
        $file_name = $order->order_number . '-' . $order->first_name . '.pdf';
        $pdf = PDF::loadview('backend.order.pdf', compact('order'));
        return $pdf->download($file_name);
    }

    // Income chart
    public function incomeChart(Request $request)
    {
        $year = \Carbon\Carbon::now()->year;
        $items = Order::with(['items'])->whereYear('created_at', $year)
                     ->where('status', 'delivered')
                     ->get()
                     ->groupBy(function($d) {
                         return \Carbon\Carbon::parse($d->created_at)->format('m');
                     });

        $result = [];
        foreach ($items as $month => $item_collections) {
            foreach ($item_collections as $item) {
                $amount = $item->items->sum('amount');
                $m = intval($month);
                isset($result[$m]) ? $result[$m] += $amount : $result[$m] = $amount;
            }
        }

        $data = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthName = date('F', mktime(0, 0, 0, $i, 1));
            $data[$monthName] = (!empty($result[$i])) ? number_format((float)($result[$i]), 2, '.', '') : 0.0;
        }

        return $data;
    }
}
