<?php

namespace App\Models;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Relations\Relation;

class AtsQueue extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'id';
    protected $table = 'ats_queues';
    protected $guarded = [];
    protected $attributes = [
        'comment' => '',
        'strategy' => 'random',
        'off_time1' => '09:00:00',
        'off_time2' => '22:00:00',
        'is_work' => false,
    ];
    
    public function unload()
    {
        return $this->belongsTo(Unload::class);
    }
    
    public function atsGroup()
    {
        return $this->belongsTo(AtsGroup::class);
    }
    
    /**
     * Компания создавшая очередь
     * @method organization
     * @return Relation
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
    
    /**
     * Компании привязанные к очереди по роли
     * @method organizations
     * @return Relation
     */
    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'lnk_ats_queue__organization');
    }
    
    public function callerIdsIn()
    {
        return $this->hasMany(SipCallerId::class)->with('atsUser');
    }
    
    public function callerIdsOper()
    {
        return $this->belongsToMany(SipCallerId::class, 'lnk_ats_queue__sip_caller_id')->withPivot('sorting')->with('atsUser', 'status');
    }
    
    public function history()
    {
        return $this->morphMany(History::class, 'ats_queues', 'reference_table', 'reference_id', 'id')->get();
    }

}
