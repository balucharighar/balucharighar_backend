<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'price',
        'quantity',
    ];

    // 🔹 OrderItem -> Order (MANY TO ONE)
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // 🔹 OrderItem -> Product (MANY TO ONE)
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
