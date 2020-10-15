<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class LnkRoleEntityParam extends Model
{

	 protected $fillable = [
		'id',
		'role_id',
		'entity_id',
		'entity_param_id'
	];

	protected $primaryKey = 'id';

    protected $table = 'lnk_role__entity_param';

}
