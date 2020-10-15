<?php

namespace App\Models;

class AtsUser extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'id';
    protected $table = 'ats_users';
    protected $guarded = [];
    
    protected $attributes = [
        'is_work' => true,
        'port' => 5060,
        'comment' => "",
        'out_calls' => false,
        'option_in_call' => true,
    ];
    
    public function atsGroup()
    {
        return $this->belongsTo(AtsGroup::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class)->with('organization');
    }

    public function sipCallerIds()
    {
        return $this->hasMany(SipCallerId::class);
    }
    
    public function history()
    {
        return $this->morphMany(History::class, 'ats_users', 'reference_table', 'reference_id', 'id')->with('users')->orderBy('id','DESC')->get();
    }
    
    public function status()
    {
        // return $this->hasMany(UserStatusLog::class)->with('status');
        return $this->hasMany(UserStatusLog::class)->orderBy('created_at', 'desc')->limit(1)->with('status');
    }

}
