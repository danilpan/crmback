<?php
namespace App\Repositories;

use App\Models\Sale;

class SalesRepository extends Repository
{
    public function model()
    {
        return Sale::class;
    }
}