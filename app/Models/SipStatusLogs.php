<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class SipStatusLogs extends Model
{


	 protected $fillable = [
		'id',
		'sip_id'
		'status_id'
	];

	protected $primaryKey = 'id';
    protected $table = 'user_status_logs';
    
}
