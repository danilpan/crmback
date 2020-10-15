<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class RoleGroup extends Model
{


	 protected $fillable = [
		'id',
		'name',
		'description',
		'creator_organization_id'
	];

	protected $primaryKey = 'id';

    protected $table = 'role_group';

}
