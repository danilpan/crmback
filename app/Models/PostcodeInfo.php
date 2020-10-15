<?php

namespace App\Models;

class PostcodeInfo extends Model
{

	protected $fillable = [
		'postcode',
		'delivery_type_id',
		'comment',
		'time',
		'price'
	];		

	public $timestamps = false;

}

