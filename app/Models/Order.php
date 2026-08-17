<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'miaccounts_external_id',
        'user_id',
        'order_number',
        'customer_name',
        'customer_mobile',
        'customer_email',
        'customer_address',
        'shipping_city',
        'shipping_area',
        'billing_address_id',
        'shipping_address_id',
        'subtotal_amount',
        'vat_amount',
        'discount_amount',
        'coupon_code',
        'shipping_amount',
        'total_amount',
        'payment_method',
        'payment_status',
        'order_status',
        'notes',
    ];

    public static $orderStatuses = [
        'pending', 'confirmed', 'ready_to_ship', 'shipped', 'delivered', 'cancelled', 'returned',
    ];

    public static $paymentStatuses = ['due', 'paid', 'partially_paid', 'refunded'];

    public static $orderStatusTransitions = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['ready_to_ship', 'cancelled'],
        'ready_to_ship' => ['shipped', 'cancelled'],
        'shipped' => ['delivered'],
        'delivered' => ['returned'],
        'cancelled' => [],
        'returned' => [],
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * The account that placed this order, null for a guest order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The saved billing address referenced by this order, null for a guest order.
     */
    public function billing(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'billing_address_id');
    }

    /**
     * The saved shipping address referenced by this order, null for a guest order.
     */
    public function shipping(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }
}
