@php
    $allowedNext = \App\Models\Order::$orderStatusTransitions[$order->order_status] ?? [];
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Order {{ $order->order_number }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-md bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-md bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-900 mb-4">Customer</h3>
                <dl class="grid grid-cols-2 gap-y-2 text-sm">
                    <dt class="text-gray-500">Name</dt>
                    <dd class="text-gray-900">{{ $order->customer_name }}</dd>
                    <dt class="text-gray-500">Mobile</dt>
                    <dd class="text-gray-900">{{ $order->customer_mobile }}</dd>
                    <dt class="text-gray-500">Email</dt>
                    <dd class="text-gray-900">{{ $order->customer_email ?: '—' }}</dd>
                    <dt class="text-gray-500">Address</dt>
                    <dd class="text-gray-900">{{ $order->customer_address }} {{ $order->shipping_area }} {{ $order->shipping_city }}</dd>
                </dl>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-900 mb-4">Items</h3>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                            <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                            <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Rate</th>
                            <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">VAT</th>
                            <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($order->items as $item)
                            <tr>
                                <td class="px-2 py-2">{{ $item->product_name }}</td>
                                <td class="px-2 py-2">{{ $item->quantity }}</td>
                                <td class="px-2 py-2">${{ number_format($item->rate, 2) }}</td>
                                <td class="px-2 py-2">${{ number_format($item->vat_amount, 2) }}</td>
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
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-gray-900 mb-2">Order Status</h3>
                    <p class="text-sm text-gray-500 mb-4">Current: <span class="capitalize font-medium text-gray-900">{{ str_replace('_', ' ', $order->order_status) }}</span></p>

                    @if (count($allowedNext))
                        <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="flex items-center gap-2">
                            @csrf
                            @method('PATCH')
                            <select name="order_status" class="rounded-md border-gray-300 shadow-sm text-sm">
                                @foreach ($allowedNext as $next)
                                    <option value="{{ $next }}" class="capitalize">{{ str_replace('_', ' ', $next) }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="px-3 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                                Update
                            </button>
                        </form>
                    @else
                        <p class="text-sm text-gray-400">No further transitions available.</p>
                    @endif
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-gray-900 mb-2">Payment Status</h3>
                    <p class="text-sm text-gray-500 mb-4">Current: <span class="capitalize font-medium text-gray-900">{{ str_replace('_', ' ', $order->payment_status) }}</span></p>

                    <form method="POST" action="{{ route('admin.orders.payment-status', $order) }}" class="flex items-center gap-2">
                        @csrf
                        @method('PATCH')
                        <select name="payment_status" class="rounded-md border-gray-300 shadow-sm text-sm">
                            @foreach (\App\Models\Order::$paymentStatuses as $status)
                                <option value="{{ $status }}" class="capitalize" @selected($status === $order->payment_status)>{{ str_replace('_', ' ', $status) }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="px-3 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                            Update
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
