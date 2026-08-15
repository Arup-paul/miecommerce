<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::latest();

        if ($request->filled('status')) {
            $query->where('order_status', $request->input('status'));
        }

        $orders = $query->paginate(20)->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'statuses' => Order::$orderStatuses,
            'currentStatus' => $request->get('status'),
        ]);
    }

    public function show(Order $order)
    {
        $order->load('items');

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'order_status' => ['required', 'in:' . implode(',', Order::$orderStatuses)],
        ]);

        $allowedNext = Order::$orderStatusTransitions[$order->order_status] ?? [];

        if (! in_array($data['order_status'], $allowedNext, true)) {
            return back()->with('error', "Cannot move an order from '{$order->order_status}' to '{$data['order_status']}'.");
        }

        $order->update(['order_status' => $data['order_status']]);

        return back()->with('status', 'Order status updated.');
    }

    public function updatePaymentStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'payment_status' => ['required', 'in:' . implode(',', Order::$paymentStatuses)],
        ]);

        $order->update(['payment_status' => $data['payment_status']]);

        return back()->with('status', 'Payment status updated.');
    }
}
