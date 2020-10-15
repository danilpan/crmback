<?php

namespace App\Models;

class OrderStatus extends Model
{


	 protected $fillable = [
		'id',
		'order_id',
		'status_id',
		'status_type',
		'user_id',
		'created_at'
	];

	protected $primaryKey = 'id';

    protected $table = 'order_status';

}
