<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'gateway',
        'gateway_order_id',
        'snap_token',
        'redirect_url',
        'transaction_status',
        'payment_type',
        'fraud_status',
        'paid_at',
        'raw_notification',
    ];

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'raw_notification' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
