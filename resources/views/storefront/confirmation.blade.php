@extends('layouts.storefront')

@section('title', 'Order Confirmation')

@section('content')
    <div class="bg-white rounded-lg border border-gray-200 p-6 max-w-2xl">
        <h1 class="text-2xl font-semibold text-gray-900">Thank you, {{ $order->customer_name }}!</h1>
        <p class="mt-2 text-gray-600">Your order has been placed successfully.</p>

        <dl class="mt-4 grid grid-cols-2 gap-y-2 text-sm">
            <dt class="text-gray-500">Order Number</dt>
            <dd class="text-gray-900 font-medium">{{ $order->order_number }}</dd>
            <dt class="text-gray-500">Order Status</dt>
            <dd class="text-gray-900 font-medium capitalize">{{ str_replace('_', ' ', $order->order_status) }}</dd>
            <dt class="text-gray-500">Payment Status</dt>
            <dd class="text-gray-900 font-medium capitalize">{{ str_replace('_', ' ', $order->payment_status) }}</dd>
            <dt class="text-gray-500">Payment Method</dt>
            <dd class="text-gray-900 font-medium capitalize">{{ str_replace('_', ' ', $order->payment_method) }}</dd>
        </dl>

        @if (empty($order->billing) && empty($order->shipping))
            <h2 class="mt-6 text-lg font-semibold text-gray-900">Delivery Address</h2>
            <p class="mt-2 text-sm text-gray-700">
                {{ $order->customer_address }}
                @if (! empty($order->shipping_city)), {{ $order->shipping_city }}@endif
                @if (! empty($order->shipping_area)), {{ $order->shipping_area }}@endif
            </p>
        @else
            <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Billing Address</h2>
                    @include('storefront.partials.address-block', ['address' => $order->billing])
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Shipping Address</h2>
                    @include('storefront.partials.address-block', ['address' => $order->shipping])
                </div>
            </div>
        @endif

        <h2 class="mt-6 text-lg font-semibold text-gray-900">Items</h2>
        <table class="mt-2 min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Rate</th>
                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach ($order->items as $item)
                    <tr>
                        <td class="px-2 py-2">{{ $item->product_name }}</td>
                        <td class="px-2 py-2">{{ $item->quantity }}</td>
                        <td class="px-2 py-2">${{ number_format($item->rate, 2) }}</td>
                        <td class="px-2 py-2">${{ number_format($item->total_amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <dl class="mt-6 space-y-1 text-sm max-w-xs ml-auto">
            <div class="flex justify-between">
                <dt class="text-gray-500">Subtotal</dt>
                <dd class="text-gray-900">${{ number_format($order->subtotal_amount, 2) }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-500">VAT</dt>
                <dd class="text-gray-900">${{ number_format($order->vat_amount, 2) }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-500">Discount</dt>
                <dd class="text-gray-900">-${{ number_format($order->discount_amount, 2) }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-500">Shipping</dt>
                <dd class="text-gray-900">${{ number_format($order->shipping_amount, 2) }}</dd>
            </div>
            <div class="flex justify-between font-semibold text-gray-900 pt-2 border-t border-gray-200">
                <dt>Total</dt>
                <dd>${{ number_format($order->total_amount, 2) }}</dd>
            </div>
        </dl>

        <a href="{{ route('home') }}" class="mt-6 inline-block text-indigo-600 hover:underline text-sm">Continue shopping</a>
    </div>
@endsection
