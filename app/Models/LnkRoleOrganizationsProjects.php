<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class LnkRoleOrganizationsProjects extends Model
{

	 protected $fillable = [
		'id',
		'role_id',
		'organization_id',
		'project_id',
		'is_deduct_organization',
		'is_deduct_project'
	];

	protected $primaryKey = 'id';

    protected $table = 'lnk_role__organization_projects';

}
