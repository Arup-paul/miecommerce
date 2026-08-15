<div>
    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
    <input type="text" name="name" id="name" value="{{ old('name', $product->name ?? '') }}" required
           class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
    <select name="category_id" id="category_id" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <option value="">Select a category</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id ?? '') == $category->id)>{{ $category->name }}</option>
        @endforeach
    </select>
    @error('category_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label for="short_description" class="block text-sm font-medium text-gray-700">Short Description</label>
    <input type="text" name="short_description" id="short_description" value="{{ old('short_description', $product->short_description ?? '') }}"
           class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    @error('short_description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label for="full_description" class="block text-sm font-medium text-gray-700">Full Description</label>
    <textarea name="full_description" id="full_description" rows="4"
              class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('full_description', $product->full_description ?? '') }}</textarea>
    @error('full_description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div class="grid grid-cols-3 gap-4">
    <div>
        <label for="price" class="block text-sm font-medium text-gray-700">Price</label>
        <input type="number" step="0.01" min="0" name="price" id="price" value="{{ old('price', $product->price ?? '') }}" required
               class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('price') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="vat_rate" class="block text-sm font-medium text-gray-700">VAT Rate (%)</label>
        <input type="number" step="0.01" min="0" max="100" name="vat_rate" id="vat_rate" value="{{ old('vat_rate', $product->vat_rate ?? 0) }}" required
               class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('vat_rate') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="stock_quantity" class="block text-sm font-medium text-gray-700">Stock Quantity</label>
        <input type="number" min="0" name="stock_quantity" id="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}" required
               class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('stock_quantity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
</div>

<div>
    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
    <select name="status" id="status" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <option value="active" @selected(old('status', $product->status ?? 'active') === 'active')>Active</option>
        <option value="inactive" @selected(old('status', $product->status ?? 'active') === 'inactive')>Inactive</option>
    </select>
    @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label for="image" class="block text-sm font-medium text-gray-700">Image</label>
    @if (! empty($product) && $product->image_path)
        <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}" class="mt-2 h-24 w-24 object-cover rounded-md">
    @endif
    <input type="file" name="image" id="image" accept="image/*" class="mt-1 w-full text-sm">
    @error('image') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
</div>
