<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class CartService
{
    protected string $sessionKey = 'cart';

    public function add(int $productId, int $qty): void
    {
        $cart = $this->raw();
        $cart[$productId] = ($cart[$productId] ?? 0) + $qty;
        session()->put($this->sessionKey, $cart);
    }

    public function update(int $productId, int $qty): void
    {
        $cart = $this->raw();

        if (! isset($cart[$productId])) {
            return;
        }

        $cart[$productId] = $qty;
        session()->put($this->sessionKey, $cart);
    }

    public function remove(int $productId): void
    {
        $cart = $this->raw();
        unset($cart[$productId]);
        session()->put($this->sessionKey, $cart);
    }

    public function items(): Collection
    {
        $cart = $this->raw();

        if (empty($cart)) {
            return collect();
        }

        return Product::whereIn('id', array_keys($cart))
            ->get()
            ->map(function (Product $product) use ($cart) {
                $quantity = $cart[$product->id];
                $lineTotal = round($product->price * $quantity, 2);

                return (object) [
                    'product' => $product,
                    'quantity' => $quantity,
                    'line_total' => $lineTotal,
                ];
            });
    }

    public function grandTotal(): float
    {
        return round($this->items()->sum('line_total'), 2);
    }

    public function isEmpty(): bool
    {
        return empty($this->raw());
    }

    public function clear(): void
    {
        session()->forget($this->sessionKey);
    }

    protected function raw(): array
    {
        return session()->get($this->sessionKey, []);
    }
}
