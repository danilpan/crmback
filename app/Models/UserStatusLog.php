<?php

namespace App\Models;
use App\Models\AtsStatus;

class UserStatusLog extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'user_status_logs';
    protected $guarded = [];
    
    public function atsUser()
    {
        return $this->belongsTo(AtsUser::class);
    }
    
    public function status()
    {
        return $this->belongsTo(AtsStatus::class);
    }
}
