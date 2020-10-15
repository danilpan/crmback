<?php

namespace App\Models;

use DB;

//use Illuminate\Database\Eloquent\Model;


class LnkRoleStatus extends Model
{

	 protected $fillable = [
		'id',
		'role_id',
		'status_id',
		'is_view',
		'is_can_set'
	];

	protected $primaryKey = 'id';

    protected $table = 'lnk_role__status';

    public function statuses()
    {
    	/*DB::enableQueryLog();
        $test = $this->belongsTo(Status::class);
        print_r(DB::getQueryLog());*/
        return $this->belongsTo(Status::class,'status_id');
    } 

    public function roles()
    {
    	/*DB::enableQueryLog();
        $test = $this->hasOne(Role::class);
        dd(DB::getQueryLog());*/
        return $this->belongsTo(Role::class,'role_id');
    } 

}
