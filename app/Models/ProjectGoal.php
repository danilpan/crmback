<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class ProjectGoal extends Model
{


	 protected $fillable = [
	    'id',
	    'project_id',
	    'name',
	    'call_center_id',
	    'geo_id',
	    'price',
	    'price_currency_id',
	    'action_payment',
	    'action_payment_currency_id',
	    'web_master_payment',
	    'web_master_payment_currency_id',
	    'is_private',
	    'additional_payment',
	    'additional_payment_currency_id',
        'min_price',
        'max_price'
	];

	protected $primaryKey = 'id';

    protected $table = 'project_goal';

    public function geo()
    {
        return $this->belongsTo(Geo::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    
    public function project_goal_scripts(){
        return $this->hasMany(ProjectGoalScript::class, 'project_goal_id');
    }

}
