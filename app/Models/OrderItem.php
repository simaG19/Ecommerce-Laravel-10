<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\AttributeValue;

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

    /**
     * Each item belongs to a product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Each item belongs to an order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
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

                // Find the attribute value
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
            \Log::error('Error getting selected attributes for order item ' . $this->id . ': ' . $e->getMessage());
            return collect(); // Return empty collection on error
        }

        return $selectedAttributes;
    }

    /**
     * Check if order item has selected attributes
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
