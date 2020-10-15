<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhoneCodes extends Model
{
    protected $fillable = [
        "id",
        "country_code",
        "city_code",
        "code_original",
        "provider",
        "region",
        "time_zone",
        "type"
    ];
}
