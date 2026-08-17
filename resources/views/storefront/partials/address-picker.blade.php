<div class="border border-gray-200 rounded-md p-4 space-y-3" data-address-picker="{{ $side }}">
    <h3 class="text-sm font-semibold text-gray-900">{{ $heading }}</h3>

    @foreach ($savedAddresses as $savedAddress)
        <label class="flex items-start gap-2 text-sm text-gray-700">
            <input type="radio" name="{{ $side }}_address_id" value="{{ $savedAddress->id }}"
                   {{ (string) old($side . '_address_id') === (string) $savedAddress->id ? 'checked' : '' }}
                   class="mt-1 border-gray-300 text-indigo-600 focus:ring-indigo-500" data-address-option>
            <span>
                <span class="font-medium text-gray-900">{{ $savedAddress->recipient_name }}</span>
                @if (! empty($savedAddress->label))
                    <span class="text-xs text-gray-500">({{ $savedAddress->label }})</span>
                @endif
                <br>
                {{ $savedAddress->address_line }}
                @if (! empty($savedAddress->city)), {{ $savedAddress->city }}@endif
                @if (! empty($savedAddress->area)), {{ $savedAddress->area }}@endif
                <br>
                <span class="text-gray-500">{{ $savedAddress->phone }}</span>
            </span>
        </label>
    @endforeach

    <label class="flex items-center gap-2 text-sm text-gray-700">
        <input type="radio" name="{{ $side }}_address_id" value=""
               {{ empty(old($side . '_address_id')) ? 'checked' : '' }}
               class="border-gray-300 text-indigo-600 focus:ring-indigo-500" data-address-option>
        <span>Use a new address</span>
    </label>

    <div class="space-y-3 pl-6" data-address-form>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="{{ $side }}_recipient_name" class="block text-sm font-medium text-gray-700">Recipient Name</label>
                <input type="text" name="{{ $side }}_recipient_name" id="{{ $side }}_recipient_name" value="{{ old($side . '_recipient_name') }}"
                       class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @error($side . '_recipient_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="{{ $side }}_phone" class="block text-sm font-medium text-gray-700">Phone</label>
                <input type="text" name="{{ $side }}_phone" id="{{ $side }}_phone" value="{{ old($side . '_phone') }}"
                       class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @error($side . '_phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label for="{{ $side }}_address_line" class="block text-sm font-medium text-gray-700">Address</label>
            <input type="text" name="{{ $side }}_address_line" id="{{ $side }}_address_line" value="{{ old($side . '_address_line') }}"
                   class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @error($side . '_address_line') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-3 gap-4">
            <div>
                <label for="{{ $side }}_city" class="block text-sm font-medium text-gray-700">City</label>
                <input type="text" name="{{ $side }}_city" id="{{ $side }}_city" value="{{ old($side . '_city') }}"
                       class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label for="{{ $side }}_area" class="block text-sm font-medium text-gray-700">Area</label>
                <input type="text" name="{{ $side }}_area" id="{{ $side }}_area" value="{{ old($side . '_area') }}"
                       class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label for="{{ $side }}_label" class="block text-sm font-medium text-gray-700">Label (optional)</label>
                <input type="text" name="{{ $side }}_label" id="{{ $side }}_label" value="{{ old($side . '_label') }}"
                       class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>

        <label for="{{ $side }}_save_as_default" class="inline-flex items-center">
            <input type="checkbox" name="{{ $side }}_save_as_default" id="{{ $side }}_save_as_default" value="1"
                   {{ old($side . '_save_as_default') ? 'checked' : '' }}
                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
            <span class="ml-2 text-sm text-gray-700">Save as my default {{ $side }} address</span>
        </label>
    </div>
</div>
