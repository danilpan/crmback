<?php
namespace App\Services;

use App\Repositories\ProjectGoalRepository;
use App\Models\User;
use RuntimeException;
use Auth;
use App\Queries\PermissionQuery;

class ProjectGoalService extends Service
{
    protected $projectGoalRepository;
    protected $permissionQuery;
        
    
    public function __construct(
        ProjectGoalRepository $projectGoalRepository, 
        PermissionQuery $permissionQuery
    )
    {
        $this->projectGoalRepository = $projectGoalRepository;
        $this->permissionQuery = $permissionQuery;
    }        

    public function getGoalId($geo_id, $project_id = 0)
    {       
        $goals = $this->projectGoalRepository->findAllBy('project_id', $project_id);
        $ru_goal = [];
        foreach ($goals as $goal) {
            if($goal['geo_id']==$geo_id)return $goal;
            if($goal['geo_id']==180)$ru_goal = $goal;
        }
        if(!empty($ru_goal))return $ru_goal;        
        if(count($goals)>0)return $goals[0];
        return [];
    }

    protected function getSearchRepository()
    {
        return $this->geoRepository;
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