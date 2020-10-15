<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'id',
        'uniqued_import_id',
        'product_code',
        'order_id',
        'product_id',
        'comment',
        'name',
        'product_price',
        'price',
        'prime_price',
        'is_cart',
        'upsale',
        'upsale_autor',
        'manager_id',
        'weight',
        'quantity',
        'quantity_price',
        'article'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

}
