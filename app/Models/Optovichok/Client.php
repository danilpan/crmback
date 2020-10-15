<?php
namespace App\Models\Optovichok;

use App\Models\Model;
use App\Models\OrderAdvertSource;
use App\Models\Organization;

class Client extends Model
{
    protected $table = 'clients';

    protected $fillable = [
        'id',
        'client_name',
        'phone',
        'iin',
        'organization_id',
        'type',
        'advert_source_id'
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function advert_source()
    {
        return $this->belongsTo(OrderAdvertSource::class, 'advert_source_id');
    }

    public function client_type()
    {
        return $this->belongsTo(ClientType::class, 'type');
    }
}