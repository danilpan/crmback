<?php
namespace App\Repositories;

use App\Models\ProjectGoal;
use Bosnadev\Repositories\Eloquent\Repository as BaseRepository;


class ProjectGoalRepository extends BaseRepository
{
    public function model()
    {
        return ProjectGoal::class;
    }

  
}
