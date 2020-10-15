<?php
namespace App\Models;

class Attempt extends Model
{
    protected $table = 'attempts';

    protected $fillable = [
        'source',
        'body',
        'image',
        'organization_id'
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }
}