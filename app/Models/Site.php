<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    protected $fillable = [
        'import_id',
        'title',
        'project_id',
        'url'
    ];
}