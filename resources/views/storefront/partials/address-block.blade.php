@if (empty($address))
    <p class="mt-2 text-sm text-gray-500">Not provided.</p>
@else
    <p class="mt-2 text-sm text-gray-700">
        <span class="font-medium text-gray-900">{{ $address->recipient_name }}</span><br>
        {{ $address->phone }}<br>
        {{ $address->address_line }}
        @if (! empty($address->city)), {{ $address->city }}@endif
        @if (! empty($address->area)), {{ $address->area }}@endif
    </p>
@endif
