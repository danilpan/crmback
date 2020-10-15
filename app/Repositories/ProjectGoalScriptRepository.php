<?php
namespace App\Repositories;

use App\Models\ProjectGoalScript;
use Bosnadev\Repositories\Eloquent\Repository as BaseRepository;


class ProjectGoalScriptRepository extends BaseRepository
{
    public function model()
    {
        return ProjectGoalScript::class;
    }

  
}
