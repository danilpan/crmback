<?php

namespace App\Models;


class Unload extends Model
{
    protected $fillable = [
        'name',
        'comment',
        'organization_id',
        'api_key',
        'config',
        'is_work'
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
    
    public function atsQueue()
    {
        return $this->hasOne(AtsQueue::class);
    }

}
