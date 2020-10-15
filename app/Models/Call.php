<?php

namespace App\Models;

class Call extends Model
{
    protected $fillable = [
        'id',
        'key',
        'organization_id',
        'queue_id',
        'step_id',
        'order_id',
        'weight',
        'call_type',
        'sip',
        'phone',
        'dst',
        'record_link',
        'rule_id',
        'ats_group_id',
        'record_time',
        'time',
        'user_id',
        'billing_time',
        'duration_time',
        'disposition',
        'addmember',
        'agentdump',
        'agentlogin',
        'agentlogoff',
        'completeagent',
        'completecaller',
        'configreload',
        'connect',
        'enterqueue',
        'exitempty',
        'caexitwithkeyll',
        'exitwithtimeout',
        'ringnoanswer',
        'abandon'
    ];

    protected $dates = [
        'time'
    ]; 

    public $timestamps  = false;

    public $incrementing = false;

    public $primaryKey  = 'id';

    public function queue()
    {
        return $this->belongsTo(AtsQueue::class, 'queue_id');
    }

    public function call_statuses()
    {
        return $this->hasMany(CallStatus::class)->orderBy('time');
    }

    public function manager()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
