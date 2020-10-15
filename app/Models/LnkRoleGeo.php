<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LnkRoleGeo extends Model
{
    protected $fillable = [
        'id',
        'role_id',
        'geo_id',
        'is_deduct_geo'
    ];

    protected $primaryKey = 'id';

    protected $table = 'lnk_role__geo';

}
