<?php

namespace App\Models;

class History extends Model
{

	protected $table = 'history';

	protected $fillable = [
		'reference_table',
		'reference_id',
		'actor_id',
		'body'
	];			

    public function orders()
    {
        return $this->morphTo();
    }

    public function users()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

}

