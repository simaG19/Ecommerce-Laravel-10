<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable=['user_id','product_id','order_id','quantity','amount','price','status', 'attribute_options'];

    // public function product(){
    //     return $this->hasOne('App\Models\Product','id','product_id');
    // }
    // public static function getAllProductFromCart(){
    //     return Cart::with('product')->where('user_id',auth()->user()->id)->get();
    // }


      protected $casts = [
      'attribute_options' => 'array',
    ];
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    public function order(){
        return $this->belongsTo(Order::class,'order_id');
    }


    public function getAttributeOptionsAttribute($value)
    {
        \Log::info('Getting attribute_options:', ['raw_value' => $value, 'type' => gettype($value)]);
        return $value;
    }
}
