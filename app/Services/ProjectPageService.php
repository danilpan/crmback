<?php
namespace App\Services;

use App\Repositories\ProjectPageRepository;
use App\Repositories\ProjectsRepository;
use App\Models\User;
use App\Queries\PermissionQuery;

class ProjectPageService extends Service
{
    protected $projectPageRepository;
    protected $projectsRepository;
    protected $permissionQuery;
 
    public function __construct(
		ProjectPageRepository $projectPageRepository,
        ProjectsRepository $projectsRepository, 
        PermissionQuery $permissionQuery
	)
    {
        $this->projectPageRepository = $projectPageRepository;
		$this->projectsRepository = $projectsRepository;
        $this->permissionQuery = $permissionQuery;
    }

	private function reindexProject($projectPage){
		 $project = $this->projectsRepository->find($projectPage->project_id);
         $this->projectsRepository->reindexModel($project, true);
	}

    public function create($data, $reindex = false)
    {
        $projectPage = $this->projectPageRepository->create($data);

        if ($projectPage) {
            if ($reindex) {
				$this->reindexProject($projectPage);
            }

            return $projectPage;
        }

        return false;
    }

    public function delete($id, $reindex = false)
    {

		$projectPage =$this->projectPageRepository->find($id);
		$result = $this->projectPageRepository->delete($id);
        if ($result) {
            if ($reindex) {
				$this->reindexProject($projectPage);
            }
        }
        return $result;
    }

    protected function getSearchRepository()
    {
        return $this->projectPageRepository;
    }

    protected function addSearchConditions(User $user=null,array $filters=null)
    {
        return $filters;
    }
    protected function getPermissionQuery(){
        return $this->permissionQuery;
    }

    protected function getExportToExcelLib(){
        return null;
    }

}
