<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class OrderDeliveryInfo extends Model
{
	 protected $fillable = [
		'order_id',
        'delivery_id',
        'd_type',
        'track_number',
        'status',
        'status_id',
        'dt_send',
        'vesban',
        'ves_tarif',
        'str_tarif',
        'comment',
        's_dt_send',
        's_status',
        's_status_id_2',
        'postcode',
        'region',
        'ops',
        's_comment',
        'tracking',
        'is_work',
        'is_error',
        'type'
	];

	protected $casts    = [
        'dt_send'=> 'datetime',
        's_dt_send'=> 'datetime'
    ];

	protected $primaryKey = 'id';

    protected $table = 'order_delivery_info';

}
