<?php

namespace App\Services;

use App\Models\Coupon;
use Illuminate\Validation\ValidationException;

class CouponService
{
    /**
     * The discount amount a valid coupon applies to the given subtotal, capped so it never
     * exceeds the subtotal for either coupon type.
     *
     * @param  \App\Models\Coupon  $coupon
     * @param  float  $subtotal
     */
    public function apply(Coupon $coupon, float $subtotal)
    {
        if ($coupon->type === 'percent') {
            return min(round($subtotal * $coupon->value / 100, 2), $subtotal);
        }

        return min($coupon->value, $subtotal);
    }

    /**
     * Re-validate the full rule set and increment usage inside the caller's transaction, using a
     * locked read so an expired/limit-hit coupon under concurrent checkouts only lets one succeed.
     *
     * @param  string  $code
     * @param  float  $subtotal
     */
    public function reserve(string $code, float $subtotal)
    {
        $coupon = Coupon::where('code', $code)->where('status', 'active')->lockForUpdate()->first();

        $this->checkRules($coupon, $subtotal);

        $coupon->increment('times_used');

        return $coupon;
    }

    /**
     * Guard clauses a coupon must pass before it can be reserved.
     *
     * @param  \App\Models\Coupon|null  $coupon
     * @param  float  $subtotal
     */
    protected function checkRules($coupon, float $subtotal)
    {
        if (empty($coupon)) {
            throw ValidationException::withMessages(['coupon_code' => 'This coupon code is not valid.']);
        }

        if (! empty($coupon->starts_at) && $coupon->starts_at->isFuture()) {
            throw ValidationException::withMessages(['coupon_code' => 'This coupon is not active yet.']);
        }

        if (! empty($coupon->expires_at) && $coupon->expires_at->isPast()) {
            throw ValidationException::withMessages(['coupon_code' => 'This coupon has expired.']);
        }

        if (! empty($coupon->min_order_amount) && $subtotal < $coupon->min_order_amount) {
            throw ValidationException::withMessages(['coupon_code' => "This coupon requires a minimum order of {$coupon->min_order_amount}."]);
        }

        if (! empty($coupon->usage_limit) && $coupon->times_used >= $coupon->usage_limit) {
            throw ValidationException::withMessages(['coupon_code' => 'This coupon has reached its usage limit.']);
        }
    }
}
