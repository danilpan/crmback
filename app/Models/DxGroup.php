<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class DxGroup extends Model
{
	 protected $fillable = [
		'key',
        'count',
        'items',
        'summary'
	];

}
