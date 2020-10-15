<?php

namespace App\Models;
use App\Models\AtsUser;
use App\Models\UserStatusLog;

class SipCallerId extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'id';
    protected $table = 'sip_caller_ids';
    protected $guarded = [];
    
    public function sip()
    {
        return $this->belongsTo(Sip::class);
    }
    
    public function atsUser()
    {
        return $this->belongsTo(AtsUser::class)->with(['user', 'status' => function ($query) {
            $query->orderBy('created_at', 'desc')->first();
        }]);
    }
    
    public function status()
    {
        return $this->hasManyThrough(UserStatusLog::class, AtsUser::class, 'id', 'ats_user_id', 'ats_user_id')->with('status');
    }
    
    /**
     * Очереди
     * 
     * @method queues
     * @return Collection
     */
    // public function queues()
    // {
    //     return $this->hasMany(Queue::class);
    // }
    
    public function atsQueue()
    {
        return $this->belongsTo(AtsQueue::class);
    }
    
    public function atsQueues()
    {
        return $this->belongsToMany(AtsQueue::class, 'lnk_ats_queue__sip_caller_id')->withPivot('sorting');
    }
}
