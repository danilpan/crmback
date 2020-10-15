<?php
namespace App\Services;

use App\Repositories\ProjectGoalScriptRepository;
use App\Models\User;
use App\Models\ProjectGoalScript;
use RuntimeException;
use Auth;
use DB;
use App\Queries\PermissionQuery;

class ProjectGoalScriptService extends Service
{
    protected $projectGoalScriptRepository;
    protected $permissionQuery;
        
    
    public function __construct(
        ProjectGoalScriptRepository $projectGoalScriptRepository, 
        PermissionQuery $permissionQuery
    )
    {
        $this->projectGoalScriptRepository = $projectGoalScriptRepository;
        $this->permissionQuery = $permissionQuery;
    }        

    public function getScriptIdMinViews($goal_id)
    {       
         $items = $this->projectGoalScriptRepository->findWhere(["project_goal_id"=>$goal_id,"status"=>true]);    
         $min = $items->where('views',$items->min('views'))->first(); 
         return $min['id'];
    }

    public function addScriptViews($script_id)
    {       
         $script = ProjectGoalScript::find($script_id);
         if(isset($script))$script->increment('views');
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