<?php
namespace App\Models;

class QueueStateHistory extends Model
{
    protected $table = 'queue_state_history';

    protected $fillable = [
        'queue_id',
        'main',
        'online',
        'speak',
        'option_in_calls'
    ];

}