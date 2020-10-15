<?php
namespace App\Repositories;

use App\Models\DxGroup;
use Bosnadev\Repositories\Eloquent\Repository as BaseRepository;
use Bosnadev\Repositories\Contracts\RepositoryInterface;


class DxGroupRepository extends BaseRepository implements  RepositoryInterface
{
    public function model()
    {
        return DxGroup::class;
    }

  
}