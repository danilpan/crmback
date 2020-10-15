<?php

namespace App\Models;

class AtsGroup extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'id';
    protected $table = 'ats_groups';

    protected $fillable = [
        'name',
        'description',
        'is_work',
        'ats_id'
    ];
    
    public function organizations()
    {
        return $this->belongsToMany('App\Models\Organization', 'lnk_ats_group__organization');
    }
    
    public function ats()
    {
        return $this->belongsTo(Ats::class);
    }
    
    public function sips()
    {
        return $this->hasMany(Sip::class);
    }
    
    public function atsUsers()
    {
        return $this->hasMany(AtsUser::class);
    }
    
    public function atsQueue()
    {
        return $this->hasOne(AtsQueue::class);
    }
}
