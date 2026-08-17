<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'label',
        'recipient_name',
        'phone',
        'address_line',
        'city',
        'area',
        'is_default_billing',
        'is_default_shipping',
    ];

    protected $casts = [
        'is_default_billing' => 'boolean',
        'is_default_shipping' => 'boolean',
    ];

    /**
     * The account this address belongs to, null for a one-off guest address.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
