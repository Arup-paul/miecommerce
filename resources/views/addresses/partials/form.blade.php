<div>
    <x-input-label for="recipient_name" :value="__('Recipient Name')" />
    <x-text-input id="recipient_name" name="recipient_name" type="text" class="mt-1 block w-full" :value="old('recipient_name', empty($address) ? '' : $address->recipient_name)" required />
    <x-input-error class="mt-2" :messages="$errors->get('recipient_name')" />
</div>

<div>
    <x-input-label for="phone" :value="__('Phone')" />
    <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', empty($address) ? '' : $address->phone)" required />
    <x-input-error class="mt-2" :messages="$errors->get('phone')" />
</div>

<div>
    <x-input-label for="address_line" :value="__('Address')" />
    <x-text-input id="address_line" name="address_line" type="text" class="mt-1 block w-full" :value="old('address_line', empty($address) ? '' : $address->address_line)" required />
    <x-input-error class="mt-2" :messages="$errors->get('address_line')" />
</div>

<div>
    <x-input-label for="city" :value="__('City')" />
    <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" :value="old('city', empty($address) ? '' : $address->city)" />
    <x-input-error class="mt-2" :messages="$errors->get('city')" />
</div>

<div>
    <x-input-label for="area" :value="__('Area')" />
    <x-text-input id="area" name="area" type="text" class="mt-1 block w-full" :value="old('area', empty($address) ? '' : $address->area)" />
    <x-input-error class="mt-2" :messages="$errors->get('area')" />
</div>

<div>
    <x-input-label for="label" :value="__('Label (optional)')" />
    <x-text-input id="label" name="label" type="text" class="mt-1 block w-full" :value="old('label', empty($address) ? '' : $address->label)" />
    <x-input-error class="mt-2" :messages="$errors->get('label')" />
</div>
