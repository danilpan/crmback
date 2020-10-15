<?php
namespace App\Repositories;

use App\Models\EntityParam;
use Bosnadev\Repositories\Eloquent\Repository as BaseRepository;


class EntityParamsRepository extends BaseRepository
{
    public function model()
    {
        return EntityParam::class;
    }

  
}