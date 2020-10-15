<?php

namespace App\Models;

class Provider extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'id';
    protected $table = 'providers';
    protected $guarded = [];
    protected $attributes = [
        'comment' => '',
        'img' => ''
    ];
}
