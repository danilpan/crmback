<?php

namespace App\Models;

class ProjectCategory extends Model
{

	 protected $fillable = [
		'id',
		'organization_id',
		'name',
		'is_work'
	];

	protected $primaryKey = 'id';

    protected $table = 'project_category';

	public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

}
