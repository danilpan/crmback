<?php

namespace App\Models;

class Ats extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'id';
    protected $table = 'ats';
    
    protected $fillable = [
        'id',
        'ip',
        'key',
        'name',
        'description',
        'is_work',
        'is_default'
    ];
    
}
