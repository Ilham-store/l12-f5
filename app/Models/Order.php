<?php

namespace App\Models;

use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        'invoice_number',
        'customer_name',
        'total',
        'status',
    ];

    protected static function booted()
    {
        static::creating(function ($order) {
            $order->invoice_number ??=
                'INV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));
        });

        static::creating(function ($order) {
            $order->invoice_number ??=
                'INV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));
        });
    
        static::saved(function ($order) {
            $order->updateQuietly([
                'total' => $order->items()->sum('subtotal'),
            ]);
        });
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function recalculateTotal()
{
    $this->update([
        'total' => $this->items()->sum('subtotal'),
    ]);
}
}

