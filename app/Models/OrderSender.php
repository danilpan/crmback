<?php
namespace App\Models;

class OrderSender extends Model
{
    protected $table = 'order_senders';

    protected $fillable = [
        'organization_id',
        'name',
        'iin',
        'phone',
        'is_work'
    ];

    public function organization(){
        return $this->belongsTo(Organization::class, 'organization_id');
    }


}