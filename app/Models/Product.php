<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'category_id',
        'short_description',
        'description',
        'image',
        'price',
        'discount_type',
        'discount_value',
        'final_price',
        'stock',
        'sku',
        'demo_link',
        'is_active',
    ];

    protected $casts = [
        'price' => 'float',
        'discount_value' => 'float',
        'final_price' => 'float',
        'is_active' => 'boolean',
    ];

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
