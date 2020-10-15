<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class ProjectGasket extends Model
{


	 protected $fillable = [
		'id',
		'project_id',
		'name',
		'link'
	];

	public function orders()
    {
        return $this->hasMany(Order::class);
    }

	protected $primaryKey = 'id';

    protected $table = 'project_gasket';
}
