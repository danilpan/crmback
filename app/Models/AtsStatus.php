<?php

namespace App\Models;

class AtsStatus extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'id';
    protected $table = 'ats_statuses';

    protected $fillable = [
        'name_en',
        'name_ru',
        'comment'
    ];
}
