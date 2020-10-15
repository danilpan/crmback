<?php

namespace App\Models;


class DeviceType extends Model
{
    protected $table = 'device_types';

    public $timestamps = false;

    protected $primaryKey = 'id';

    protected $fillable = ['name', 'is_show'];

    public function orders(){
        return $this->hasMany(Order::class);
    }

}
