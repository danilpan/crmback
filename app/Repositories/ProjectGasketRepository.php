<?php
namespace App\Repositories;

use App\Models\ProjectGasket;
use Bosnadev\Repositories\Eloquent\Repository as BaseRepository;


class ProjectGasketRepository extends BaseRepository
{
    public function model()
    {
        return ProjectGasket::class;
    }

  
}
