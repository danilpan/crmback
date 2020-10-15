<?php
namespace App\Repositories\Optovichok;

use App\Models\Optovichok\ClientType;
use App\Repositories\Repository;

class ClientTypeRepository extends Repository
{
    public function model()
    {
        return ClientType::class;
    }
}