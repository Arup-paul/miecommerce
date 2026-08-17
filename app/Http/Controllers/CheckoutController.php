<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\AddressService;
use App\Services\CartService;
use App\Services\CouponService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    protected CartService $cart;

    protected AddressService $addresses;

    protected CouponService $coupons;

    public function __construct(CartService $cart, AddressService $addresses, CouponService $coupons)
    {
        $this->cart = $cart;
        $this->addresses = $addresses;
        $this->coupons = $coupons;
    }

    public function index()
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $items = $this->cart->items();
        $grandTotal = $this->cart->grandTotal();
        $customer = Auth::user();
        $savedAddresses = $this->savedAddresses();

        return view('storefront.checkout', compact('items', 'grandTotal', 'customer', 'savedAddresses'));
    }

    public function store(Request $request)
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $validator = $this->validateRule($request);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        $wasGuestSubmission = empty(Auth::id());
        $this->registerGuestIfRequested($data);
        $items = $this->cart->items();

        $unavailable = $items->filter(function ($item) {
            return $item->product->status !== 'active' || $item->product->stock_quantity < $item->quantity;
        });

        if ($unavailable->isNotEmpty()) {
            $names = $unavailable->pluck('product.name')->implode(', ');

            return redirect()->route('cart.index')
                ->with('error', "The following item(s) are no longer available in the requested quantity: {$names}");
        }

        $userId = Auth::id();
        $addressUserId = $wasGuestSubmission ? null : $userId;

        try {
            $order = $this->placeOrder($data, $items, $userId, $addressUserId);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        $this->cart->clear();

        return redirect()->route('orders.confirmation', $order->order_number);
    }

    /**
     * Create a user account for the current guest and log them in, when the checkout form
     * requested it, so the rest of store() naturally takes the authenticated path.
     *
     * @param  array  $data
     */
    protected function registerGuestIfRequested(array $data)
    {
        if (empty($data['create_account'])) {
            return;
        }

        if (! empty(Auth::id())) {
            return;
        }

        $user = User::create([
            'name' => $data['customer_name'],
            'email' => $data['customer_email'],
            'phone' => $data['customer_mobile'],
            'password' => Hash::make($data['password']),
        ]);

        event(new Registered($user));

        Auth::login($user);

        $this->cart->mergeSessionIntoUser($user->id);
    }

    /**
     * Create the order and its items inside one transaction, reserving the coupon if supplied.
     *
     * @param  array  $data
     * @param  \Illuminate\Support\Collection  $items
     * @param  int|null  $userId
     * @param  int|null  $addressUserId
     */
    protected function placeOrder(array $data, $items, $userId, $addressUserId)
    {
        return DB::transaction(function () use ($data, $items, $userId, $addressUserId) {
            $resolved = $this->addresses->resolveForCheckout($data, $addressUserId);
            $subtotal = 0;
            $totalVat = 0;

            $lines = $items->map(function ($item) use (&$subtotal, &$totalVat) {
                $product = $item->product;
                $lineSubtotal = $product->price * $item->quantity;
                $vatAmount = round($lineSubtotal * $product->vat_rate / 100, 2);
                $lineTotal = $lineSubtotal + $vatAmount;

                $subtotal += $lineSubtotal;
                $totalVat += $vatAmount;

                return [
                    'product' => $product,
                    'quantity' => $item->quantity,
                    'rate' => $product->price,
                    'vat_percent' => $product->vat_rate,
                    'vat_amount' => $vatAmount,
                    'total_amount' => $lineTotal,
                ];
            });

            $shippingAmount = (float) config('shop.shipping_flat_rate');
            $couponResult = $this->applyCoupon($data, $subtotal);
            $discountAmount = $couponResult['discount_amount'];
            $totalAmount = $subtotal + $totalVat + $shippingAmount - $discountAmount;

            $flat = $this->flatAddressColumns($data, $resolved['shipping']);

            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'user_id' => $userId,
                'customer_name' => $data['customer_name'],
                'customer_mobile' => $data['customer_mobile'],
                'customer_email' => $data['customer_email'] ?? null,
                'customer_address' => $flat['customer_address'],
                'shipping_city' => $flat['shipping_city'],
                'shipping_area' => $flat['shipping_area'],
                'billing_address_id' => $this->addressId($resolved['billing']),
                'shipping_address_id' => $this->addressId($resolved['shipping']),
                'subtotal_amount' => $subtotal,
                'vat_amount' => $totalVat,
                'discount_amount' => $discountAmount,
                'coupon_code' => $couponResult['coupon_code'],
                'shipping_amount' => $shippingAmount,
                'total_amount' => $totalAmount,
                'payment_method' => $data['payment_method'],
                'payment_status' => 'due',
                'order_status' => 'pending',
            ]);

            foreach ($lines as $line) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $line['product']->id,
                    'product_name' => $line['product']->name,
                    'rate' => $line['rate'],
                    'quantity' => $line['quantity'],
                    'vat_percent' => $line['vat_percent'],
                    'vat_amount' => $line['vat_amount'],
                    'discount_amount' => 0,
                    'total_amount' => $line['total_amount'],
                ]);

                $line['product']->decrement('stock_quantity', $line['quantity']);
            }

            return $order;
        });
    }

    /**
     * Reserve and apply a coupon for this checkout, or return a zero-discount result when none
     * was supplied. Reservation happens inside the caller's transaction so a usage-limited coupon
     * under concurrent checkouts only lets one succeed.
     *
     * @param  array  $data
     * @param  float  $subtotal
     */
    protected function applyCoupon(array $data, float $subtotal)
    {
        $code = $data['coupon_code'] ?? null;

        if (empty($code)) {
            return ['discount_amount' => 0, 'coupon_code' => null];
        }

        $coupon = $this->coupons->reserve($code, $subtotal);
        $discountAmount = $this->coupons->apply($coupon, $subtotal);

        return ['discount_amount' => $discountAmount, 'coupon_code' => $coupon->code];
    }

    public function confirmation(Order $order)
    {
        $order = Order::where('id', $order->id)
            ->with(['items', 'billing', 'shipping'])
            ->first();

        return view('storefront.confirmation', compact('order'));
    }

    /**
     * Validate a checkout submission, requiring address fields only where no saved address is chosen.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    protected function validateRule(Request $request)
    {
        $userId = Auth::id();

        $rules = [
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_mobile' => ['required', 'string', 'max:30'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'payment_method' => ['required', 'in:cash_on_delivery,card,mobile_banking,bank_transfer'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
        ];

        if (empty($userId)) {
            $guestRules = array_merge($rules, $this->guestAddressRules(), $this->createAccountRules($request));

            return Validator::make($request->all(), $guestRules);
        }

        $sameAsBilling = $request->boolean('shipping_same_as_billing');
        $rules = array_merge($rules, $this->sideRules($request, 'billing'), ['shipping_same_as_billing' => ['nullable', 'boolean']]);

        if (! empty($sameAsBilling)) {
            return Validator::make($request->all(), $rules);
        }

        return Validator::make($request->all(), array_merge($rules, $this->sideRules($request, 'shipping')));
    }

    /**
     * Validation rules for the guest single flat-address form, unchanged from before C2.
     */
    protected function guestAddressRules()
    {
        return [
            'customer_address' => ['required', 'string', 'max:255'],
            'shipping_city' => ['nullable', 'string', 'max:120'],
            'shipping_area' => ['nullable', 'string', 'max:120'],
            'create_account' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Validation rules for the optional "create an account" checkbox on the guest checkout form,
     * requiring an email and a confirmed password only when the checkbox was checked.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    protected function createAccountRules(Request $request)
    {
        $createAccount = $request->boolean('create_account');

        if (empty($createAccount)) {
            return [];
        }

        return [
            'customer_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    /**
     * Validation rules for one address side, switching between saved-id and new one-off fields.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $side
     */
    protected function sideRules(Request $request, string $side)
    {
        $addressId = $request->get($side . '_address_id');

        if (! empty($addressId)) {
            return [$side . '_address_id' => ['required', 'integer', 'exists:addresses,id']];
        }

        return [
            $side . '_address_id' => ['nullable', 'integer'],
            $side . '_label' => ['nullable', 'string', 'max:255'],
            $side . '_recipient_name' => ['required', 'string', 'max:255'],
            $side . '_phone' => ['required', 'string', 'max:30'],
            $side . '_address_line' => ['required', 'string', 'max:255'],
            $side . '_city' => ['nullable', 'string', 'max:120'],
            $side . '_area' => ['nullable', 'string', 'max:120'],
            $side . '_save_as_default' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Saved addresses of the authenticated user, empty for a guest.
     */
    protected function savedAddresses()
    {
        $userId = Auth::id();

        if (empty($userId)) {
            return collect();
        }

        return Address::where('user_id', $userId)->orderByDesc('id')->get();
    }

    /**
     * Flat legacy address columns, taken from the resolved shipping address when one exists.
     *
     * @param  array  $data
     * @param  \App\Models\Address|null  $shipping
     */
    protected function flatAddressColumns(array $data, $shipping)
    {
        if (empty($shipping)) {
            return [
                'customer_address' => $data['customer_address'],
                'shipping_city' => $data['shipping_city'] ?? null,
                'shipping_area' => $data['shipping_area'] ?? null,
            ];
        }

        return [
            'customer_address' => $shipping->address_line,
            'shipping_city' => $shipping->city,
            'shipping_area' => $shipping->area,
        ];
    }

    /**
     * Primary key of a resolved address, null when none was resolved.
     *
     * @param  \App\Models\Address|null  $address
     */
    protected function addressId($address)
    {
        if (empty($address)) {
            return null;
        }

        return $address->id;
    }

    protected function generateOrderNumber(): string
    {
        $prefix = 'ORD-' . now()->format('Ymd') . '-';
        $latest = Order::where('order_number', 'like', $prefix . '%')
            ->orderByDesc('order_number')
            ->first();

        $nextSequence = $latest
            ? ((int) substr($latest->order_number, strlen($prefix))) + 1
            : 1;

        return $prefix . str_pad((string) $nextSequence, 4, '0', STR_PAD_LEFT);
    }
}
