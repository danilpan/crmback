<?php
namespace App\Repositories;

use App\Models\Entity;
use Bosnadev\Repositories\Eloquent\Repository as BaseRepository;


class EntitiesRepository extends BaseRepository
{
    public function model()
    {
        return Entity::class;
    }

  
}