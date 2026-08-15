<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_name',
        'customer_mobile',
        'customer_email',
        'customer_address',
        'shipping_city',
        'shipping_area',
        'subtotal_amount',
        'vat_amount',
        'discount_amount',
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
}
