<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Shipping;
use App\User;
use PDF;
use Notification;
use Helper;
use Illuminate\Support\Str;
use App\Notifications\StatusNotification;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $orders=Order::orderBy('id','DESC')->paginate(10);
        return view('backend.order.index')->with('orders',$orders);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
{
    // 1) VALIDATION
    $request->validate([
        'first_name'         => 'required|string',
        'last_name'          => 'nullable|string',
        'screenshot'         => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        'address1'           => 'required|string',
        'address2'           => 'nullable|string',
        'coupon'             => 'nullable|numeric',
        'phone'              => 'required|numeric',
        'post_code'          => 'nullable|string',
        'email'              => 'nullable|email',
        'shipping'           => 'nullable|exists:shippings,id',
        'payment_method'     => 'required|in:cod,paypal',
    ]);

    // 2) PREVENT EMPTY CART
    $cart = Cart::where('user_id', auth()->id())
                ->whereNull('order_id')
                ->first();
    if (! $cart) {
        session()->flash('error', 'Cart is empty!');
        return back();
    }

    // 3) BUILD & SAVE ORDER
    $order = new Order();

    // -- Basic form fields:
    $order->first_name     = $request->first_name;
    $order->last_name      = $request->last_name;
    $order->address1       = $request->address1;
    $order->address2       = $request->address2;
    $order->phone          = $request->phone;
    $order->post_code      = $request->post_code;
    $order->email          = $request->email;
    $order->country        = $request->country;

    // -- Screenshot if any:
    if ($request->hasFile('screenshot')) {
        $order->screenshoot = $request
            ->file('screenshot')
            ->store('screenshots', 'public');
    }

    // -- System fields:
    $order->order_number  = 'ORD-'.strtoupper(Str::random(10));
    $order->user_id       = auth()->id();
    $order->shipping_id   = $request->shipping;
    $order->sub_total     = Helper::totalCartPrice();
    $order->quantity      = Helper::cartCount();
    $order->coupon        = session('coupon')['value'] ?? null;

    // -- Total calculation:
    $shippingCost = $request->shipping
        ? Shipping::find($request->shipping)->price
        : 0;

    $order->total_amount = Helper::totalCartPrice()
        + $shippingCost
        - ($order->coupon ?: 0);

    $order->status        = 'new';
    $order->payment_method= $request->payment_method;
    $order->payment_status= $request->payment_method === 'paypal'
                             ? 'paid'
                             : 'Unpaid';

    // finally save
    $order->save();

    // 4) ATTACH CART ITEMS, NOTIFY, CLEAR SESSIONS
    Cart::where('user_id', auth()->id())
        ->whereNull('order_id')
        ->update(['order_id' => $order->id]);

    // send admin notification
    $admin = User::where('role','admin')->first();
    $details = [
        'title'     => 'New order created',
        'actionURL' => route('order.show', $order->id),
        'fas'       => 'fa-file-alt',
    ];
    Notification::send($admin, new StatusNotification($details));

    // clear sessions
    session()->forget(['cart', 'coupon']);

    session()->flash('success','Your order has been placed successfully!');

    // redirect to payment or home
   if ($request->payment_method === 'paypal') {
    // We’ve saved the screenshot; now treat this like an offline payment
    session()->flash(
        'success',
        'Thank you! Your payment screenshot has been received and is pending verification.'
    );
    return redirect()->route('home');
}

    return redirect()->route('home');
}

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $order=Order::find($id);
        // return $order;
        return view('backend.order.show')->with('order',$order);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $order=Order::find($id);
        return view('backend.order.edit')->with('order',$order);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $order=Order::find($id);
        $this->validate($request,[
            'status'=>'required|in:new,process,delivered,cancel'
        ]);
        $data=$request->all();
        // return $request->status;
        if($request->status=='delivered'){
            foreach($order->cart as $cart){
                $product=$cart->product;
                // return $product;
                $product->stock -=$cart->quantity;
                $product->save();
            }
        }
        $status=$order->fill($data)->save();
        if($status){
            request()->session()->flash('success','Successfully updated order');
        }
        else{
            request()->session()->flash('error','Error while updating order');
        }
        return redirect()->route('order.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $order=Order::find($id);
        if($order){
            $status=$order->delete();
            if($status){
                request()->session()->flash('success','Order Successfully deleted');
            }
            else{
                request()->session()->flash('error','Order can not deleted');
            }
            return redirect()->route('order.index');
        }
        else{
            request()->session()->flash('error','Order can not found');
            return redirect()->back();
        }
    }

    public function orderTrack(){
        return view('frontend.pages.order-track');
    }

    public function productTrackOrder(Request $request){
        // return $request->all();
        $order=Order::where('user_id',auth()->user()->id)->where('order_number',$request->order_number)->first();
        if($order){
            if($order->status=="new"){
            request()->session()->flash('success','Your order has been placed. please wait.');
            return redirect()->route('home');

            }
            elseif($order->status=="process"){
                request()->session()->flash('success','Your order is under processing please wait.');
                return redirect()->route('home');

            }
            elseif($order->status=="delivered"){
                request()->session()->flash('success','Your order is successfully delivered.');
                return redirect()->route('home');

            }
            else{
                request()->session()->flash('error','Your order canceled. please try again');
                return redirect()->route('home');

            }
        }
        else{
            request()->session()->flash('error','Invalid order numer please try again');
            return back();
        }
    }

    // PDF generate
    public function pdf(Request $request){
        $order=Order::getAllOrder($request->id);
        // return $order;
        $file_name=$order->order_number.'-'.$order->first_name.'.pdf';
        // return $file_name;
        $pdf=PDF::loadview('backend.order.pdf',compact('order'));
        return $pdf->download($file_name);
    }
    // Income chart
    public function incomeChart(Request $request){
        $year=\Carbon\Carbon::now()->year;
        // dd($year);
        $items=Order::with(['cart_info'])->whereYear('created_at',$year)->where('status','delivered')->get()
            ->groupBy(function($d){
                return \Carbon\Carbon::parse($d->created_at)->format('m');
            });
            // dd($items);
        $result=[];
        foreach($items as $month=>$item_collections){
            foreach($item_collections as $item){
                $amount=$item->cart_info->sum('amount');
                // dd($amount);
                $m=intval($month);
                // return $m;
                isset($result[$m]) ? $result[$m] += $amount :$result[$m]=$amount;
            }
        }
        $data=[];
        for($i=1; $i <=12; $i++){
            $monthName=date('F', mktime(0,0,0,$i,1));
            $data[$monthName] = (!empty($result[$i]))? number_format((float)($result[$i]), 2, '.', '') : 0.0;
        }
        return $data;
    }
}
