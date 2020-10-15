<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class CallCenter extends Model
{


	 protected $fillable = [
		'id',
		'name'
	];

	protected $primaryKey = 'id';

    protected $table = 'call_center';
}
