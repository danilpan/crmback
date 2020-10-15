<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    //    
    protected $fillable = [
		'parent_id', 
        'organization_id', 
        'name', 
        'desc', 
        'title', 
        'is_work',
        'type', 
        'color',
        'sort'
    ];    


    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
    
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function children()
    {
        return $this->hasMany(Status::class, 'parent_id')->with('children')->orderBy('id');        
    }

    public function getChildIds(){
        $ids = [$this->id];

        foreach ($this->children as $child){
            $ids = array_merge($ids, $child->getChildIds());
        }

        return $ids;
    }

    public function parent()
    {
        return $this->belongsTo(Status::class, 'parent_id');
    }

    public function status_titles()
    {
        return $this->hasMany(StatusTitle::class);
    }

    public function history()
    {
        return $this->morphMany(History::class, 'statuses', 'reference_table', 'reference_id', 'id')->with('users')->orderBy('id','DESC');
    }
    
}
