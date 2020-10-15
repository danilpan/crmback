<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderImportId extends Model
{
    protected $fillable = [
        'id',
        'order_id',
        'import_id'
    ];

    public $timestamps = false;
}
