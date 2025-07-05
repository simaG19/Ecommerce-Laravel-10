<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AttributeValue;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'price',
        'quantity',
        'amount',
        'attribute_options',
        'order_id'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get attribute options as array
     */
    public function getAttributeOptionsAttribute($value)
    {
        if (empty($value) || is_null($value)) {
            return null;
        }

        try {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : null;
        } catch (\Exception $e) {
            \Log::error('Error decoding attribute_options for cart ' . $this->id . ': ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Set attribute options as JSON string
     */
    public function setAttributeOptionsAttribute($value)
    {
        if (is_null($value) || (is_array($value) && empty($value))) {
            $this->attributes['attribute_options'] = null;
        } elseif (is_array($value)) {
            $this->attributes['attribute_options'] = json_encode($value);
        } else {
            $this->attributes['attribute_options'] = $value;
        }
    }

    /**
     * Get the selected attribute values with their details
     * Always returns a collection, never null
     */
    public function getSelectedAttributesAttribute()
    {
        // Always return a collection, even if empty
        $selectedAttributes = collect();

        // Get the decoded attribute options
        $attributeOptions = $this->attribute_options;

        // Check if attribute_options exists and is not empty
        if (!$attributeOptions || !is_array($attributeOptions) || empty($attributeOptions)) {
            return $selectedAttributes;
        }

        try {
            foreach ($attributeOptions as $attrId => $valueId) {
                // Skip if valueId is null or empty
                if (empty($valueId)) {
                    continue;
                }

                // Find the attribute value - this should work with your existing structure
                $attributeValue = AttributeValue::with('attribute')->find($valueId);
                if ($attributeValue && $attributeValue->attribute) {
                    $selectedAttributes->push([
                        'attribute_name' => $attributeValue->attribute->name,
                        'value' => $attributeValue->value,
                        'price' => $attributeValue->price ?? 0,
                        'attribute_id' => $attrId,
                        'value_id' => $valueId
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error getting selected attributes for cart ' . $this->id . ': ' . $e->getMessage());
            return collect(); // Return empty collection on error
        }

        return $selectedAttributes;
    }

    /**
     * Check if cart item has selected attributes
     */
    public function hasSelectedAttributes()
    {
        $attributeOptions = $this->attribute_options;
        return $attributeOptions &&
               is_array($attributeOptions) &&
               is_countable($attributeOptions) &&
               count($attributeOptions) > 0 &&
               !empty(array_filter($attributeOptions)); // Filter out empty values
    }

    /**
     * Get formatted attribute display string
     */
    public function getAttributeDisplayAttribute()
    {
        if (!$this->hasSelectedAttributes()) {
            return '';
        }

        $attributes = $this->selectedAttributes;
        if (!$attributes || $attributes->isEmpty()) {
            return '';
        }

        return $attributes->map(function ($attr) {
            $display = $attr['attribute_name'] . ': ' . $attr['value'];
            if ($attr['price'] > 0) {
                $display .= ' (+' . number_format($attr['price'], 2) . ' ETB)';
            }
            return $display;
        })->join(', ');
    }

    /**
     * Get raw attribute options (for debugging)
     */
    public function getRawAttributeOptions()
    {
        return $this->getRawOriginal('attribute_options');
    }
}
