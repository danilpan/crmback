<?php

namespace App\Models;

class SmsProvider extends Model
{
    //
     protected $fillable = [	    
	    'organization_id',
	    'name',
	    'comment',
	    'sms_provider',
	    'data',   
	    'is_work'
	];

}
