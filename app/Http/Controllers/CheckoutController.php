<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    protected CartService $cart;

    public function __construct(CartService $cart)
    {
        $this->cart = $cart;
    }

    public function index()
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $items = $this->cart->items();
        $grandTotal = $this->cart->grandTotal();

        return view('storefront.checkout', compact('items', 'grandTotal'));
    }

    public function store(Request $request)
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_mobile' => ['required', 'string', 'max:30'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_address' => ['required', 'string', 'max:255'],
            'shipping_city' => ['nullable', 'string', 'max:120'],
            'shipping_area' => ['nullable', 'string', 'max:120'],
            'payment_method' => ['required', 'in:cash_on_delivery,card,mobile_banking,bank_transfer'],
        ]);

        $items = $this->cart->items();

        $unavailable = $items->filter(function ($item) {
            return $item->product->status !== 'active' || $item->product->stock_quantity < $item->quantity;
        });

        if ($unavailable->isNotEmpty()) {
            $names = $unavailable->pluck('product.name')->implode(', ');

            return redirect()->route('cart.index')
                ->with('error', "The following item(s) are no longer available in the requested quantity: {$names}");
        }

        $order = DB::transaction(function () use ($data, $items) {
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
            $discountAmount = 0;
            $totalAmount = $subtotal + $totalVat + $shippingAmount - $discountAmount;

            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'customer_name' => $data['customer_name'],
                'customer_mobile' => $data['customer_mobile'],
                'customer_email' => $data['customer_email'] ?? null,
                'customer_address' => $data['customer_address'],
                'shipping_city' => $data['shipping_city'] ?? null,
                'shipping_area' => $data['shipping_area'] ?? null,
                'subtotal_amount' => $subtotal,
                'vat_amount' => $totalVat,
                'discount_amount' => $discountAmount,
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

        $this->cart->clear();

        return redirect()->route('orders.confirmation', $order->order_number);
    }

    public function confirmation(Order $order)
    {
        $order->load('items');

        return view('storefront.confirmation', compact('order'));
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
