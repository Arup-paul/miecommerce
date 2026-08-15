<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Orders</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4 flex flex-wrap gap-2 text-sm">
                <a href="{{ route('admin.orders.index') }}"
                   class="px-3 py-1.5 rounded-full {{ ! $currentStatus ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 border border-gray-200' }}">
                    All
                </a>
                @foreach ($statuses as $status)
                    <a href="{{ route('admin.orders.index', ['status' => $status]) }}"
                       class="px-3 py-1.5 rounded-full capitalize {{ $currentStatus === $status ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 border border-gray-200' }}">
                        {{ str_replace('_', ' ', $status) }}
                    </a>
                @endforeach
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($orders as $order)
                            <tr>
                                <td class="px-6 py-4 text-gray-900">{{ $order->order_number }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $order->customer_name }}</td>
                                <td class="px-6 py-4 text-gray-600">${{ number_format($order->total_amount, 2) }}</td>
                                <td class="px-6 py-4">
                                    <span class="text-xs px-2 py-1 rounded-full bg-indigo-100 text-indigo-800 capitalize">
                                        {{ str_replace('_', ' ', $order->order_status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs px-2 py-1 rounded-full bg-amber-100 text-amber-800 capitalize">
                                        {{ str_replace('_', ' ', $order->payment_status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="text-indigo-600 hover:underline text-sm">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
