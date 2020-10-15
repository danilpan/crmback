<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsRule extends Model
{
    //
    protected $fillable = [	    
	    'organization_id',
	    'sms_provider_id',
	    'geo_id',
	    'name',
	    'comment',   
	    'type',
	    'is_work'
	];

	public function sms_provider()
    {
        return $this->belongsTo(SmsProvider::class);
    }
}
