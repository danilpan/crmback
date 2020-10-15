<?php

namespace App\Models;

class DeliveryType extends Model
{
    protected $fillable = [
        'organization_id',
        'name',
        'price',
        'surplus_percent',
        'is_work',
        'is_show',
        'priority',
        'postcode_info'
    ];

   public function projects()
    {
        return $this->belongsToMany(
            Project::class,
            'delivery_type_project',
            'delivery_type_id',
            'project_id'
        )->withTimestamps()->withPivot('geo_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'delivery_types_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

}
