<?php
namespace App\Repositories;

use App\Models\Role;
use Bosnadev\Repositories\Eloquent\Repository as BaseRepository;


class RolesRepository extends BaseRepository
{
    public function model()
    {
        return Role::class;
    }

  
}