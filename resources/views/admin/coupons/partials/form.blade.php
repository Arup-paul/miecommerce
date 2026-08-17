<div>
    <label for="code" class="block text-sm font-medium text-gray-700">Code</label>
    <input type="text" name="code" id="code" value="{{ old('code', $coupon->code ?? '') }}" required
           class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    @error('code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
        <select name="type" id="type" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="percent" @selected(old('type', $coupon->type ?? 'percent') === 'percent')>Percent</option>
            <option value="fixed" @selected(old('type', $coupon->type ?? 'percent') === 'fixed')>Fixed</option>
        </select>
        @error('type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="value" class="block text-sm font-medium text-gray-700">Value</label>
        <input type="number" step="0.01" min="0" name="value" id="value" value="{{ old('value', $coupon->value ?? '') }}" required
               class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('value') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
</div>

<div>
    <label for="min_order_amount" class="block text-sm font-medium text-gray-700">Minimum Order Amount (optional)</label>
    <input type="number" step="0.01" min="0" name="min_order_amount" id="min_order_amount" value="{{ old('min_order_amount', $coupon->min_order_amount ?? '') }}"
           class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    @error('min_order_amount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label for="starts_at" class="block text-sm font-medium text-gray-700">Starts At (optional)</label>
        <input type="datetime-local" name="starts_at" id="starts_at" value="{{ old('starts_at', empty($coupon) ? '' : optional($coupon->starts_at)->format('Y-m-d\TH:i')) }}"
               class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('starts_at') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="expires_at" class="block text-sm font-medium text-gray-700">Expires At (optional)</label>
        <input type="datetime-local" name="expires_at" id="expires_at" value="{{ old('expires_at', empty($coupon) ? '' : optional($coupon->expires_at)->format('Y-m-d\TH:i')) }}"
               class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('expires_at') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
</div>

<div>
    <label for="usage_limit" class="block text-sm font-medium text-gray-700">Usage Limit (optional)</label>
    <input type="number" min="1" name="usage_limit" id="usage_limit" value="{{ old('usage_limit', $coupon->usage_limit ?? '') }}"
           class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    @error('usage_limit') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
    <select name="status" id="status" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <option value="active" @selected(old('status', $coupon->status ?? 'active') === 'active')>Active</option>
        <option value="inactive" @selected(old('status', $coupon->status ?? 'active') === 'inactive')>Inactive</option>
    </select>
    @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
</div>
