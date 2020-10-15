<?php
namespace App\Services;

use App\Repositories\ProjectCategoryKcRepository;
use App\Models\User;
use App\Queries\PermissionQuery;

class ProjectCategoryKcService extends Service
{
    protected $projectCategoryKcRepository;
    protected $permissionQuery;
    
    public function __construct(ProjectCategoryKcRepository $projectCategoryKcRepository, PermissionQuery $permissionQuery)
    {
        $this->projectCategoryKcRepository = $projectCategoryKcRepository;
        $this->permissionQuery = $permissionQuery;
    }

    protected function addSearchConditions(User $user=null,array $filters=null)
    {
        return $filters;
    }

    protected function getSearchRepository()
    {
        return $this->projectCategoryKcRepository;
    }

    protected function getPermissionQuery(){
        return $this->permissionQuery;
    }

    protected function getExportToExcelLib(){
        return null;
    }
}
