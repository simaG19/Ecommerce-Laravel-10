<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Display user's orders
     */
    public function index()
    {
        $orders = Order::where('user_id', Auth::id())
                      ->with(['items.product'])
                      ->orderBy('created_at', 'desc')
                      ->paginate(10);

        return view('frontend.pages.user.order.index', compact('orders'));
    }

    /**
     * Show specific order details
     */
    public function show($id)
    {
        $order = Order::where('user_id', Auth::id())
                     ->where('id', $id)
                     ->with(['items.product', 'items.product.attributes.values'])
                     ->firstOrFail();

        return view('frontend.pages.user.order.show', compact('order'));
    }
}
