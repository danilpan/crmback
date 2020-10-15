<?php
namespace App\Models;

class LnkGeoProduct extends Model
{
    protected $fillable = [
        'id',
        'geo_id',
        'product_id'
    ];

    protected $primaryKey = 'id';

    protected $table = 'lnk_geo_product';
}