@extends('layouts.storefront')

@section('title', 'Your Cart')

@section('content')
    <h1 class="text-2xl font-semibold text-gray-900 mb-6">Your Cart</h1>

    @if ($items->isEmpty())
        <p class="text-gray-500 mb-4">Your cart is empty.</p>
        <a href="{{ route('home') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">
            Add to Cart
        </a>
    @else
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Line Total</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($items as $item)
                        <tr>
                            <td class="px-4 py-4">
                                <a href="{{ route('products.show', $item->product) }}" class="font-medium text-gray-900 hover:text-indigo-600">
                                    {{ $item->product->name }}
                                </a>
                            </td>
                            <td class="px-4 py-4 text-gray-600">${{ number_format($item->product->price, 2) }}</td>
                            <td class="px-4 py-4">
                                <form method="POST" action="{{ route('cart.update', $item->product) }}" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="{{ $item->product->stock_quantity }}"
                                           class="w-20 rounded-md border-gray-300 shadow-sm text-sm">
                                    <button type="submit" class="text-sm text-indigo-600 hover:underline">Update</button>
                                </form>
                            </td>
                            <td class="px-4 py-4 font-medium text-gray-900">${{ number_format($item->line_total, 2) }}</td>
                            <td class="px-4 py-4 text-right">
                                <form method="POST" action="{{ route('cart.remove', $item->product) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm text-red-600 hover:underline">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6 flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4">
            <a href="{{ route('home') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">
                Add to Cart
            </a>

            <div class="w-full sm:w-72 bg-white rounded-lg border border-gray-200 p-4">
                <div class="flex justify-between text-lg font-semibold text-gray-900">
                    <span>Total</span>
                    <span>${{ number_format($grandTotal, 2) }}</span>
                </div>
                <a href="{{ route('checkout.index') }}" class="mt-4 block w-full text-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                    Proceed to Checkout
                </a>
            </div>
        </div>
    @endif
@endsection
