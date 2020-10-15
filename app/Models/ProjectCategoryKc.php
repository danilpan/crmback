<?php

namespace App\Models;

class ProjectCategoryKc extends Model
{

	 protected $fillable = [
		'id',
		'title',
		'is_work',
		'key'
	];

	protected $primaryKey = 'id';

    protected $table = 'project_category_kc';


}
