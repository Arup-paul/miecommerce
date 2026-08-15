<div>
    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
    <input type="text" name="name" id="name" value="{{ old('name', $category->name ?? '') }}" required
           class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
    <select name="status" id="status" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <option value="active" @selected(old('status', $category->status ?? 'active') === 'active')>Active</option>
        <option value="inactive" @selected(old('status', $category->status ?? 'active') === 'inactive')>Inactive</option>
    </select>
    @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
</div>
