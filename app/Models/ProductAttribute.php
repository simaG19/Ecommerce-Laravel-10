<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAttribute extends Model
{
    protected $fillable = ['product_id','name'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // <<-- this now resolves to the class you just created
    public function values()
    {
        return $this->hasMany(AttributeValue::class);
    }
}
