<?php
namespace App\Repositories;

use App\Models\CallCenter;
use Bosnadev\Repositories\Eloquent\Repository as BaseRepository;


class CallCenterRepository extends BaseRepository
{
    public function model()
    {
        return CallCenter::class;
    }

  
}
