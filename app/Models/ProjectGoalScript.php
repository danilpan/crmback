<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class ProjectGoalScript extends Model
{
	 protected $fillable = [	    
	    'project_goal_id',
	    'name',
	    'link',	    
	    'cross_sales',
	    'status',
	    'views'
	];	 

	protected $primaryKey = 'id';

    protected $table = 'project_goal_scripts';   

    public $timestamps = false;

    public function cross_sales()
    {
        return $this->belongsToMany(Product::class,'project_goal_script_products','project_goal_script_id','product_id')->withPivot('note', 'price', 'type');
    }   

}
