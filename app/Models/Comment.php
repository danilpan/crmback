<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    //    
    protected $fillable = [
		'text',
        'order_id',
        'user_id'
    ];        

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
