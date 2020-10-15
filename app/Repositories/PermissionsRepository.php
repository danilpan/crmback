<?php
namespace App\Repositories;

use App\Models\Permission;

class PermissionsRepository extends Repository
{
    public function model()
    {
        return Permission::class;
    }
}