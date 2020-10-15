<?php
namespace App\Repositories;

use App\Models\OrderImportId;

class OrderImportIdsRepository extends Repository
{
    public function model()
    {
        return OrderImportId::class;
    }
}