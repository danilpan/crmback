<?php

namespace App\Models;

class ProjectPage extends Model
{


	 protected $fillable = [
		'id',
		'project_id',
		'name',
        'link',
        'organization_id'
	];

	protected $primaryKey = 'id';

    protected $table = 'project_page';


    public function phones()
    {
        return $this->hasMany(ProjectPagePhone::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
    
}
