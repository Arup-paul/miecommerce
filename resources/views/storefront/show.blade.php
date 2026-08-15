@extends('layouts.storefront')

@section('title', $product->name)

@section('content')
    <nav class="text-sm text-gray-500 mb-6">
        <a href="{{ route('categories.show', $product->category) }}" class="hover:text-gray-700">{{ $product->category->name }}</a>
        <span class="mx-1">/</span>
        <span class="text-gray-700">{{ $product->name }}</span>
    </nav>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 bg-white rounded-lg border border-gray-200 p-6">
        <div class="aspect-square bg-gray-100 rounded-md flex items-center justify-center">
            @if ($product->image_path)
                <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}" class="w-full h-full object-cover rounded-md">
            @else
                <span class="text-gray-400 text-sm">No image</span>
            @endif
        </div>

        <div>
            <h1 class="text-2xl font-semibold text-gray-900">{{ $product->name }}</h1>
            <p class="mt-2 text-xl font-semibold text-gray-900">${{ number_format($product->price, 2) }}</p>

            @if ($product->short_description)
                <p class="mt-4 text-gray-600">{{ $product->short_description }}</p>
            @endif

            @if ($product->stock_quantity > 0 && $product->status === 'active')
                <form method="POST" action="{{ route('cart.add', $product) }}" class="mt-6 flex flex-wrap items-end gap-3">
                    @csrf
                    <div>
                        <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                        <input type="number" name="quantity" id="quantity" value="1" min="1" max="{{ $product->stock_quantity }}"
                               class="mt-1 w-24 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 whitespace-nowrap">
                        Add to Cart
                    </button>
                </form>
                <p class="mt-2 text-sm text-gray-500">{{ $product->stock_quantity }} in stock</p>
            @else
                <p class="mt-6 text-sm text-red-600 font-medium">Currently unavailable</p>
            @endif

            @if ($product->full_description)
                <div class="mt-8 border-t border-gray-200 pt-6">
                    <h2 class="text-sm font-semibold text-gray-900 mb-2">Description</h2>
                    <p class="text-gray-600 whitespace-pre-line">{{ $product->full_description }}</p>
                </div>
            @endif
        </div>
    </div>
@endsection
