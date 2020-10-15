<?php

namespace App\Models;

class CallStatus extends Model
{
    
    protected $fillable = [        
        'id',
        'call_id',
        'user_id',
        'status',
        'agent',
        'time'
    ];

    protected $dates = [
        'time'
    ];

    public $timestamps  = false;

    public function call()
    {
        return $this->belongsTo(Call::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    
}
