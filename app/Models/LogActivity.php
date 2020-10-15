<?php


namespace App\Models;


class LogActivity extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = 'log_activities';

    protected $casts    = [        
        'created_at'=> 'datetime'        
    ];

    protected $fillable = [
        'action', 'url', 'method', 'ip', 'user_agent', 'user_id', 'info', 'referer'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}