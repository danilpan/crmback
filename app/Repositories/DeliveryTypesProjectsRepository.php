<?php
namespace App\Repositories;

use App\Models\DeliveryTypeProjects;
use Bosnadev\Repositories\Eloquent\Repository as BaseRepository;


class DeliveryTypesProjectsRepository extends BaseRepository
{
    public function model()
    {
        return DeliveryTypeProjects::class;
    }

  
}
