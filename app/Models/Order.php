<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'customer_name',
        'table_number',
        'source',
        'status',
        'payment_status',
        'total',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'confirmed_at' => 'datetime',
            'total' => 'integer',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function refreshProgressStatus(): void
    {
        $items = $this->items()->get(['status']);

        if ($items->isEmpty() || ! in_array($this->status, ['confirmed', 'partially_done', 'done'], true)) {
            return;
        }

        $doneCount = $items->where('status', 'done')->count();
        $newStatus = match (true) {
            $doneCount === 0 => 'confirmed',
            $doneCount === $items->count() => 'done',
            default => 'partially_done',
        };

        $this->forceFill(['status' => $newStatus])->save();
    }
}
