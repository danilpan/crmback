<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class UserStatusLogs extends Model
{


	 protected $fillable = [
		'id',
		'ats_user_id',
		'status_id'
	];

	protected $primaryKey = 'id';
    protected $table = 'user_status_logs';
}
