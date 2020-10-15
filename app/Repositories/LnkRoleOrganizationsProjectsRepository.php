<?php
namespace App\Repositories;

use App\Models\LnkRoleOrganizationsProjects;
use Bosnadev\Repositories\Eloquent\Repository as BaseRepository;

class LnkRoleOrganizationsProjectsRepository extends BaseRepository
{
    public function model()
    {
        return LnkRoleOrganizationsProjects::class;
    }
}