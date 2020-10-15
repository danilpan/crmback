<?php
namespace App\Repositories;

use App\Models\ProjectPagePhone;
use Bosnadev\Repositories\Eloquent\Repository as BaseRepository;


class ProjectPagePhoneRepository extends BaseRepository
{
    public function model()
    {
        return ProjectPagePhone::class;
    }

  
}
