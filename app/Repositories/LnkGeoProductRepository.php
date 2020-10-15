<?php
namespace App\Repositories;

use App\Models\LnkGeoProduct;

class LnkGeoProductRepository extends Repository
{
    public function model()
    {
        return LnkGeoProduct::class;
    }

}