<?php

namespace App\Models;
use DB;

class ProjectPagePhone  extends Model
{

	protected $fillable = [
		'project_page_id',
		'phone'
	];

    protected $table = 'project_page_phone';

    public function pages()
    {    	
        return $this->belongsTo(ProjectPage::class,'project_page_id');
    }

}
