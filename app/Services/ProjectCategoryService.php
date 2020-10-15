<?php
namespace App\Services;

use App\Repositories\ProjectCategoryRepository;
use App\Models\User;
use App\Queries\PermissionQuery;

class ProjectCategoryService extends Service
{
    protected $projectCategoryRepository;
    protected $permissionQuery;
    
    public function __construct(ProjectCategoryRepository $projectCategoryRepository, PermissionQuery $permissionQuery)
    {
        $this->projectCategoryRepository = $projectCategoryRepository;
        $this->permissionQuery = $permissionQuery;
    }

    public function create($data, $reindex = false)
    {
        $item = $this->projectCategoryRepository->create($data);

        if ($item) {
            if ($reindex) {
                $this->projectCategoryRepository->reindexModel($item, true);
            }

            return $item;
        }

        return false;
    }

    public function update($id, $data, $reindex = false)
    {
        $item = null; 
        $result = $this->projectCategoryRepository->update($data, $id);
        if ($result) {
            $item = $this->projectCategoryRepository->find($id);

            if ($reindex) {
                $this->projectCategoryRepository->reindexModel($item, true);
            }
            
        }

        return $item;
    }

    public function delete($id, $reindex = false)
    {
        $item = $this->projectCategoryRepository->find($id);
        $result = $this->projectCategoryRepository->delete($id);
        if ($result) {
            if ($reindex) {
                $this->projectCategoryRepository->deleteFromIndex($item, true);
            }
            
        }

        return $result;
    }

    protected function addSearchConditions(User $user=null,array $filters=null)
    {
        return $filters;
    }

    protected function getSearchRepository()
    {
        return $this->projectCategoryRepository;
    }

    protected function getPermissionQuery(){
        return $this->permissionQuery;
    }

    protected function getExportToExcelLib(){
        return null;
    }
}
