<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Addresses') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-md bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <header class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-medium text-gray-900">{{ __('Saved Addresses') }}</h2>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ __('Manage the addresses you can pick from at checkout.') }}
                        </p>
                    </div>
                    <a href="{{ route('addresses.create') }}" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                        {{ __('Add Address') }}
                    </a>
                </header>

                @if ($addresses->isEmpty())
                    <p class="mt-6 text-sm text-gray-500">{{ __('You have not saved any addresses yet.') }}</p>
                @else
                    <ul class="mt-6 divide-y divide-gray-200">
                        @foreach ($addresses as $address)
                            <li class="py-4 flex items-start justify-between gap-4">
                                <div class="text-sm text-gray-700">
                                    <p class="font-medium text-gray-900">
                                        {{ $address->recipient_name }}
                                        @if (! empty($address->label))
                                            <span class="text-xs text-gray-500">({{ $address->label }})</span>
                                        @endif
                                    </p>
                                    <p>
                                        {{ $address->address_line }}
                                        @if (! empty($address->city)), {{ $address->city }}@endif
                                        @if (! empty($address->area)), {{ $address->area }}@endif
                                    </p>
                                    <p class="text-gray-500">{{ $address->phone }}</p>
                                    <p class="mt-1 space-x-2">
                                        @if ($address->is_default_billing)
                                            <span class="inline-flex items-center rounded-full bg-indigo-50 text-indigo-700 px-2 py-0.5 text-xs">{{ __('Default billing') }}</span>
                                        @endif
                                        @if ($address->is_default_shipping)
                                            <span class="inline-flex items-center rounded-full bg-green-50 text-green-700 px-2 py-0.5 text-xs">{{ __('Default shipping') }}</span>
                                        @endif
                                    </p>
                                </div>

                                <div class="flex flex-col items-end gap-2 text-sm shrink-0">
                                    <a href="{{ route('addresses.edit', $address) }}" class="text-indigo-600 hover:underline">{{ __('Edit') }}</a>

                                    @if (! $address->is_default_billing)
                                        <form method="post" action="{{ route('addresses.set-default', $address) }}">
                                            @csrf
                                            @method('patch')
                                            <input type="hidden" name="kind" value="billing">
                                            <button type="submit" class="text-gray-600 hover:underline">{{ __('Set default billing') }}</button>
                                        </form>
                                    @endif

                                    @if (! $address->is_default_shipping)
                                        <form method="post" action="{{ route('addresses.set-default', $address) }}">
                                            @csrf
                                            @method('patch')
                                            <input type="hidden" name="kind" value="shipping">
                                            <button type="submit" class="text-gray-600 hover:underline">{{ __('Set default shipping') }}</button>
                                        </form>
                                    @endif

                                    <form method="post" action="{{ route('addresses.destroy', $address) }}"
                                          onsubmit="return confirm('{{ __('Delete this address?') }}')">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="text-red-600 hover:underline">{{ __('Delete') }}</button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
