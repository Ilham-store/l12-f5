<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    protected static function booted()
    {
        // AUTO SLUG SAAT CREATE
        static::creating(function ($c) {
            $c->slug ??= Str::slug($c->name);
        });
    
        // UPDATE SLUG + BLOKIR NONAKTIF CATEGORY
        static::updating(function ($c) {
    
            // Update slug kalau nama berubah
            if ($c->isDirty('name')) {
                $c->slug = Str::slug($c->name);
            }
    
            // ❌ Cegah category dinonaktifkan kalau masih dipakai product
            if (
                $c->isDirty('is_active') &&
                $c->is_active === false &&
                $c->products()->exists()
            ) {
                ValidationException::withMessages([
                    'is_active' => 'Category tidak bisa dinonaktifkan karena masih digunakan oleh product.',
                ]);
                
            }
        });
    
        // AUTO NONAKTIFKAN PRODUCT KALAU CATEGORY OFF
        static::updated(function ($c) {
            if ($c->wasChanged('is_active') && $c->is_active === false) {
                $c->products()->update([
                    'is_active' => false,
                ]);
            }
        });
    }
    
}
