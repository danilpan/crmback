<?php
namespace App\Services;

use App\Repositories\ProjectPageRepository;
use App\Repositories\ProjectsRepository;
use App\Repositories\ProjectPagePhoneRepository;
use App\Models\User;

class ProjectPagePhoneService extends Service
{
    protected $projectPageRepository;
    protected $projectsRepository;
	protected $projectPagePhoneRepository;
 
    public function __construct(
		ProjectPageRepository $projectPageRepository,
		ProjectsRepository $projectsRepository,
		ProjectPagePhoneRepository $projectPagePhoneRepository
	)
    {
        $this->projectPageRepository = $projectPageRepository;
		$this->projectPagePhoneRepository = $projectPagePhoneRepository;
		$this->projectsRepository = $projectsRepository;
    }

	private function reindexProject($projectPage){
		 $project = $this->projectsRepository->find($projectPage->project_id);
         $this->projectsRepository->reindexModel($project, true);
	}

    public function create($data, $reindex = false)
    {

        $projectPagePhone = $this->projectPagePhoneRepository->create($data);
        $projectPage = $this->projectPageRepository->find($data['project_page_id']);

        if ($projectPagePhone) {
            if ($reindex) {
				$this->reindexProject($projectPage);
            }

            return $projectPagePhone;
        }

        return false;
    }

    public function delete($id, $reindex = false)
    {

        $projectPagePhone = $this->projectPagePhoneRepository->find($id);
		$projectPage =$this->projectPageRepository->find($projectPagePhone->project_page_id);
		$result = $this->projectPagePhoneRepository->delete($id);
        if ($result) {
            if ($reindex) {
				$this->reindexProject($projectPage);
            }
        }
        return $result;
    }

    protected function getSearchRepository()
    {
        return $this->projectPagePhoneRepository;
    }

    protected function addSearchConditions(User $user=null,array $filters=null)
    {
        return $filters;
    }
    
    protected function getPermissionQuery(){
        return $this->permissionQuery;
    }
    
    protected function getExportToExcelLib(){
        return $this->exportToExcel;
    }
}
