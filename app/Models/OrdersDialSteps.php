<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class OrdersDialSteps extends Model
{
	 protected $fillable = [
		'id',
		'queue_id',
		'order_id',
		'dial_step',
		'dial_time'
	];

	protected $casts    = [
        'dial_time'=> 'datetime'
    ];

	protected $primaryKey = 'id';

    protected $table = 'orders_dial_steps';

}
