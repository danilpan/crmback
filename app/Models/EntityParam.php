<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class EntityParam extends Model
{


	 protected $fillable = [
		'id',
		'parent_id',
		'entity_id',
		'name',
		'description',
		'parameter'
	];

	protected $primaryKey = 'id';

    protected $table = 'entity_params';

}
