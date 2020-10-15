<?php
namespace App\Repositories;

use App\Models\Traffic;
use Bosnadev\Repositories\Eloquent\Repository as BaseRepository;


class TrafficsRepository extends BaseRepository
{
    public function model()
    {
        return Traffic::class;
    }

  
}