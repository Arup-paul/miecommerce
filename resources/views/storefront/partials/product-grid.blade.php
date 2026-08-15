<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse ($products as $product)
        <a href="{{ route('products.show', $product) }}" class="block bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-md transition">
            <div class="aspect-square bg-gray-100 flex items-center justify-center">
                @if ($product->image_path)
                    <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                @else
                    <span class="text-gray-400 text-sm">No image</span>
                @endif
            </div>
            <div class="p-4">
                <h3 class="font-medium text-gray-900 truncate">{{ $product->name }}</h3>
                <p class="text-sm text-gray-500 mt-1">{{ $product->category->name }}</p>
                <p class="mt-2 font-semibold text-gray-900">${{ number_format($product->price, 2) }}</p>
                @if ($product->stock_quantity < 1)
                    <p class="text-xs text-red-600 mt-1">Out of stock</p>
                @endif
            </div>
        </a>
    @empty
        <p class="text-gray-500 col-span-full">No products found.</p>
    @endforelse
</div>

<div class="mt-8">
    {{ $products->links() }}
</div>
