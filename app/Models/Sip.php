<?php

namespace App\Models;

class Sip extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'id';
    protected $table = 'sips';
    protected $guarded = [];
    protected $attributes = [
        'description' => '',
        'is_work' => true
    ];
    
    public function atsGroup()
    {
        return $this->belongsTo(AtsGroup::class);
    }
    
    public function sipCallerIds()
    {
        return $this->hasMany(SipCallerId::class);
    }
    
    // public function sipStatusLogs()
    // {
    //     return $this->hasMany(SipStatusLog::class);
    // }
}
