<?php
// app/Models/ProductAttribute.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAttribute extends Model
{
    protected $fillable = [
        'product_id',
        'name'
    ];

    /**
     * Each ProductAttribute belongs to a Product
     */
   /**
     * Each ProductAttribute belongs to a Product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Each ProductAttribute has many AttributeValues
     */
    public function values()
    {
        return $this->hasMany(AttributeValue::class, 'product_attribute_id');
    }
}
