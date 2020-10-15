<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Role extends Model
{


	 protected $fillable = [
		'id',
		'name',
		'description',
		'group_id',
		'is_superuser',
		'creator_organization_id'
	];

	protected $primaryKey = 'id';

    protected $table = 'roles';


    public function entity_params()
    {
        return $this->belongsToMany(
            EntityParam::class,
            'lnk_role__entity_param',
            'role_id',
			'entity_param_id',
			'entity_id'
        )->withTimestamps();
	}
	
	public function entity_param()
    {
        return $this->belongsToMany(
            EntityParam::class,
            'lnk_role__entity_param',
            'role_id',
			'entity_param_id'
        );
    }

    public function geos(){
        return $this->belongsToMany(
            Geo::class,
            'lnk_role__geo',
            'role_id',
            'geo_id'
            );
    }
}
