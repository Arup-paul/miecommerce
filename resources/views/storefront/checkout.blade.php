@extends('layouts.storefront')

@section('title', 'Checkout')

@section('content')
    <h1 class="text-2xl font-semibold text-gray-900 mb-6">Checkout</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 bg-white rounded-lg border border-gray-200 p-6">
            <form id="checkout-form" method="POST" action="{{ route('checkout.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="customer_name" class="block text-sm font-medium text-gray-700">Full Name</label>
                    <input type="text" name="customer_name" id="customer_name" value="{{ old('customer_name', empty($customer) ? '' : $customer->name) }}" required
                           class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('customer_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="customer_mobile" class="block text-sm font-medium text-gray-700">Mobile Number</label>
                    <input type="text" name="customer_mobile" id="customer_mobile" value="{{ old('customer_mobile', empty($customer) ? '' : $customer->phone) }}" required
                           class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('customer_mobile') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="customer_email" class="block text-sm font-medium text-gray-700">Email (optional)</label>
                    <input type="email" name="customer_email" id="customer_email" value="{{ old('customer_email', empty($customer) ? '' : $customer->email) }}"
                           class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('customer_email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                @if (empty($customer))
                    <div>
                        <label for="customer_address" class="block text-sm font-medium text-gray-700">Address</label>
                        <input type="text" name="customer_address" id="customer_address" value="{{ old('customer_address') }}" required
                               class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('customer_address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="shipping_city" class="block text-sm font-medium text-gray-700">City</label>
                            <input type="text" name="shipping_city" id="shipping_city" value="{{ old('shipping_city') }}"
                                   class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="shipping_area" class="block text-sm font-medium text-gray-700">Area</label>
                            <input type="text" name="shipping_area" id="shipping_area" value="{{ old('shipping_area') }}"
                                   class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div class="pt-2">
                        <label for="create_account" class="inline-flex items-center">
                            <input type="checkbox" name="create_account" id="create_account" value="1"
                                   {{ old('create_account') ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Create an account for faster checkout next time</span>
                        </label>
                    </div>

                    <div data-create-account-section hidden>
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                            <input type="password" name="password" id="password"
                                   class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="mt-4">
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                   class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                @else
                    @error('address') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                    @include('storefront.partials.address-picker', [
                        'side' => 'billing',
                        'heading' => 'Billing Address',
                        'savedAddresses' => $savedAddresses,
                    ])

                    <div class="pt-2">
                        <label for="shipping_same_as_billing" class="inline-flex items-center">
                            <input type="checkbox" name="shipping_same_as_billing" id="shipping_same_as_billing" value="1"
                                   {{ old('shipping_same_as_billing') ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Shipping address is the same as billing</span>
                        </label>
                    </div>

                    <div data-shipping-section>
                        @include('storefront.partials.address-picker', [
                            'side' => 'shipping',
                            'heading' => 'Shipping Address',
                            'savedAddresses' => $savedAddresses,
                        ])
                    </div>
                @endif

                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-700">Payment Method</label>
                    <select name="payment_method" id="payment_method" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="cash_on_delivery">Cash on Delivery</option>
                        <option value="card">Card</option>
                        <option value="mobile_banking">Mobile Banking</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                    @error('payment_method') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                    Place Order
                </button>
            </form>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-6 h-fit">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h2>
            <ul class="space-y-3">
                @foreach ($items as $item)
                    <li class="flex justify-between text-sm">
                        <span class="text-gray-600">{{ $item->product->name }} &times; {{ $item->quantity }}</span>
                        <span class="text-gray-900 font-medium">${{ number_format($item->line_total, 2) }}</span>
                    </li>
                @endforeach
            </ul>

            <div class="mt-4 pt-4 border-t border-gray-200">
                <label for="coupon_code" class="block text-sm font-medium text-gray-700">Coupon Code (optional)</label>
                <input type="text" name="coupon_code" id="coupon_code" form="checkout-form" value="{{ old('coupon_code') }}"
                       class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @error('coupon_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="mt-4 pt-4 border-t border-gray-200 flex justify-between font-semibold text-gray-900">
                <span>Total</span>
                <span>${{ number_format($grandTotal, 2) }}</span>
            </div>
        </div>
    </div>

    @auth
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const syncPicker = function (picker) {
                    const form = picker.querySelector('[data-address-form]')
                    const selected = picker.querySelector('[data-address-option]:checked')
                    if (!form) return
                    form.hidden = selected && selected.value !== ''
                }

                const pickers = document.querySelectorAll('[data-address-picker]')
                pickers.forEach(function (picker) {
                    syncPicker(picker)
                    picker.querySelectorAll('[data-address-option]').forEach(function (option) {
                        option.addEventListener('change', function () {
                            syncPicker(picker)
                        })
                    })
                })

                const sameAsBilling = document.getElementById('shipping_same_as_billing')
                const shippingSection = document.querySelector('[data-shipping-section]')
                if (!sameAsBilling || !shippingSection) return

                const syncShipping = function () {
                    shippingSection.hidden = sameAsBilling.checked
                }

                syncShipping()
                sameAsBilling.addEventListener('change', syncShipping)
            })
        </script>
    @endauth

    @guest
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const createAccount = document.getElementById('create_account')
                const createAccountSection = document.querySelector('[data-create-account-section]')
                if (!createAccount || !createAccountSection) return

                const syncCreateAccount = function () {
                    createAccountSection.hidden = !createAccount.checked
                }

                syncCreateAccount()
                createAccount.addEventListener('change', syncCreateAccount)
            })
        </script>
    @endguest
@endsection
