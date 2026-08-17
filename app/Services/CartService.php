<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class CartService
{
    protected string $sessionKey = 'cart';

    public function add(int $productId, int $qty): void
    {
        $userId = $this->userId();

        if (empty($userId)) {
            $cart = $this->sessionRaw();
            $cart[$productId] = ($cart[$productId] ?? 0) + $qty;
            session()->put($this->sessionKey, $cart);

            return;
        }

        $row = Cart::where('user_id', $userId)->where('product_id', $productId)->first();

        if (empty($row)) {
            Cart::create([
                'user_id' => $userId,
                'product_id' => $productId,
                'quantity' => $qty,
            ]);

            return;
        }

        $row->update(['quantity' => $row->quantity + $qty]);
    }

    public function update(int $productId, int $qty): void
    {
        $userId = $this->userId();

        if (empty($userId)) {
            $cart = $this->sessionRaw();

            if (! isset($cart[$productId])) {
                return;
            }

            $cart[$productId] = $qty;
            session()->put($this->sessionKey, $cart);

            return;
        }

        Cart::where('user_id', $userId)->where('product_id', $productId)->update(['quantity' => $qty]);
    }

    public function remove(int $productId): void
    {
        $userId = $this->userId();

        if (empty($userId)) {
            $cart = $this->sessionRaw();
            unset($cart[$productId]);
            session()->put($this->sessionKey, $cart);

            return;
        }

        Cart::where('user_id', $userId)->where('product_id', $productId)->delete();
    }

    public function items(): Collection
    {
        $cart = $this->raw();

        if (empty($cart)) {
            return collect();
        }

        return Product::whereIn('id', collect($cart)->keys()->all())
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

        $userId = $this->userId();

        if (empty($userId)) {
            return;
        }

        Cart::where('user_id', $userId)->delete();
    }

    /**
     * Merge the guest session cart quantities into the given user's persisted cart rows.
     *
     * @param  int  $userId
     */
    public function mergeSessionIntoUser(int $userId): void
    {
        $sessionCart = $this->sessionRaw();

        if (empty($sessionCart)) {
            return;
        }

        $existing = Cart::where('user_id', $userId)
            ->whereIn('product_id', collect($sessionCart)->keys()->all())
            ->get()
            ->keyBy('product_id');

        collect($sessionCart)->each(function ($quantity, $productId) use ($userId, $existing) {
            $row = $existing->get($productId);

            if (empty($row)) {
                Cart::create([
                    'user_id' => $userId,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                ]);

                return;
            }

            $row->update(['quantity' => $row->quantity + $quantity]);
        });

        session()->forget($this->sessionKey);
    }

    /**
     * Current cart contents as a product_id => quantity map for the active guest or user.
     */
    protected function raw(): array
    {
        $userId = $this->userId();

        if (empty($userId)) {
            return $this->sessionRaw();
        }

        return Cart::where('user_id', $userId)
            ->pluck('quantity', 'product_id')
            ->all();
    }

    /**
     * Guest session cart contents as a product_id => quantity map.
     */
    protected function sessionRaw(): array
    {
        return session()->get($this->sessionKey, []);
    }

    /**
     * Authenticated user id, or null for a guest.
     */
    protected function userId()
    {
        if (! Auth::check()) {
            return null;
        }

        return Auth::id();
    }
}
