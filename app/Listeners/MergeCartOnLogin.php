<?php

namespace App\Listeners;

use App\Services\CartService;
use Illuminate\Auth\Events\Login;

class MergeCartOnLogin
{
    protected CartService $cart;

    public function __construct(CartService $cart)
    {
        $this->cart = $cart;
    }

    /**
     * Merge the guest session cart into the newly authenticated user's persisted cart.
     *
     * @param  \Illuminate\Auth\Events\Login  $event
     */
    public function handle(Login $event): void
    {
        $user = $event->user;

        if (empty($user)) {
            return;
        }

        $this->cart->mergeSessionIntoUser((int) $user->getAuthIdentifier());
    }
}
