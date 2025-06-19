<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'price',
        'quantity',
        'amount',
        'attribute_options',
    ];

    protected $casts = [
        'attribute_options' => 'array',
    ];

    // each item belongs to a product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // and of course to its order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
