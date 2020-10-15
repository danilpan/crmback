<?php
namespace App\Repositories;

use App\Models\LnkRoleStatus;

class LnkRoleStatusRepository extends Repository
{
    public function model()
    {
        return LnkRoleStatus::class;
    }
}