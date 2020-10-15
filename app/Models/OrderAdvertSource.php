<?php

namespace App\Models;

class OrderAdvertSource extends Model
{
    protected $fillable = [
        'name',
        'is_show'
    ];

    protected $primaryKey = 'id';
    protected $table = 'order_advert_sources';
    public $timestamps = false;

    public function order(){
        return $this->hasMany(Order::class, "source_id");
    }

}
