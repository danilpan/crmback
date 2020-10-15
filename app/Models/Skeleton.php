<?php
namespace App\Models;

class Skeleton extends Model
{
    protected $table = 'skeleton';
    protected $fillable = [
        'user_id',
        'organization_id',
        'comment',
        'line1',
        'line2',
        'line3',
        'line4',
        'line5',
        'line6',
        'line7',
        'line8',
        'line9',
        'line10',
        'line11',
        'line12',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function organization(){
        return $this->belongsTo(Organization::class);
    }
}