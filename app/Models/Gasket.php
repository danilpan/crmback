<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gasket extends Model
{
    protected $fillable = [
    	'id',
        'name',
		'url',
        'id_product',
    ];
}
