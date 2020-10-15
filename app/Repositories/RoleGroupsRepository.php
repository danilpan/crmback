<?php
namespace App\Repositories;

use App\Models\RoleGroup;
use Bosnadev\Repositories\Eloquent\Repository as BaseRepository;


class RoleGroupsRepository extends BaseRepository
{
    public function model()
    {
        return RoleGroup::class;
    }

  
}