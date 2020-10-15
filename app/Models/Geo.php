<?php

namespace App\Models;

class Geo extends Model
{


	 protected $fillable = [
		'id',
		'name_en',
		'name_ru',
		'code',
		'mask'
	];

	protected $primaryKey = 'id';

    protected $table = 'geo';

}
