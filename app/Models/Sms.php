<?php

namespace App\Models;

class Sms extends Model
{
    //    
	protected $fillable = [	    
	    'organization_id',
	    'order_id',
	    'user_id',
	    'sms_provider_id',
	    'phone',   
	    'message',
	    'status',
	    'sms_provider_status',
	    'service_id',
	    'type'
	];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->belongsTo(Order::class);
    }

    public function sms_provider()
    {
        return $this->belongsTo(SmsProvider::class);
    }
}
