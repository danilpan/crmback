<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryTypeProjects extends Model
{

	 protected $fillable = [
		'project_id',
		'delivery_type_id'
	];

	protected $primaryKey = 'id';

    protected $table = 'project_page_phone';

	public function page()
	{
		return $this->belongsTo(ProjectPage::class);
	}

}
