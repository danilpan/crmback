<?php

namespace App\Models;

class OutRoute extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'id';
    protected $table = 'out_routes';
    protected $guarded = [];
    protected $attributes = [
        'comment' => "",
        'is_work' => true,
        'prefix' => "",
        'replace_count' => 0,
        'trunks2' => "",
    ];
    
    public function atsGroup()
    {
        return $this->belongsTo(AtsGroup::class);
    }
    
    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
    
    public function history()
    {
        return $this->morphMany(History::class, $this->table, 'reference_table', 'reference_id', 'id')->get();
    }
}