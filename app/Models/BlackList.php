<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Model;

class BlackList extends Model
{
    
    protected $table = 'black_list';
    //    
    protected $fillable = [
		'phone',
        'user_id'
    ];        

    public function user()
    {
        return $this->belongsTo(User::class);
    }  
    
}
