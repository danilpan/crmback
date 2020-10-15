<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Model;

class StatusTitle extends Model
{
    //    
    protected $fillable = [
		'title',
        'organization_id',
        'status_id'
    ];        

    public $timestamps = false;

}
