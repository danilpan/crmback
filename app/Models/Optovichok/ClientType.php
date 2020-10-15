<?php
namespace App\Models\Optovichok;

use App\Models\Model;

class ClientType extends Model
{
    protected $table = 'client_type';

    protected $fillable = [
        'title'
    ];
}