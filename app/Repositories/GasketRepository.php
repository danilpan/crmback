<?php
namespace App\Repositories;

use App\Models\Gasket;

class GasketRepository extends Repository
{
    public function model()
    {
        return Gasket::class;
    }
}