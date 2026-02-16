<?php

namespace App\Models;

use App\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;


class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'price',
        'stock',
        'description',
        'images',
        'is_active',
    ];

    protected $casts = [
        'images' => 'array',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    protected static function booted()
    {
        static::creating(function ($product) {
            if (! $product->slug) {
                $product->slug = Str::slug($product->name);
            }
        });

        static::updating(function ($product) {
            if ($product->isDirty('name')) {
                $product->slug = Str::slug($product->name);
            }
        });

        static::saving(function ($product) {
            if (
                $product->is_active &&
                $product->category &&
                ! $product->category->is_active
            ) {
                throw ValidationException::withMessages([
                    'is_active' => 'Product tidak bisa diaktifkan karena category tidak aktif.',
                ]);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}


