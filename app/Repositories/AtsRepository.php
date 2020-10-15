<?php
namespace App\Repositories;

use App\Models\Ats;

class AtsRepository extends Repository
{
    public function model()
    {
        return Ats::class;
    }
}