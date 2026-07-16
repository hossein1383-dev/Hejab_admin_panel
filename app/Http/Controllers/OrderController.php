<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::latest()
            ->with(['address', 'orderItems'])
            ->paginate(3);

        return view('orders.index', compact('orders'));
    }

    public function edit(Order $order)
    {
        $order->load(['address.city', 'orderItems.product']);

        return view('orders.edit', compact('order'));
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|integer|between:0,3',
            'payment_status' => 'required|boolean',
        ]);

        $order->update([
            'status' => $request->status,
            'payment_status' => $request->payment_status,
        ]);

        return redirect()->route('order_index')->with('success', 'سفارش با موفقیت بروزرسانی شد.');
    }
}
