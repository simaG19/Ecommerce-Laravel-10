<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttributeValue extends Model
{
    // allow mass‑assignment on these columns:
    protected $fillable = [
        'product_attribute_id',
        'value','price'
    ];

    /**
     * Each AttributeValue belongs to one ProductAttribute.
     */
    public function attribute()
    {
        return $this->belongsTo(
            ProductAttribute::class,
            'product_attribute_id'
        );
    }
}
