<?php
namespace App\Services;

use App\Repositories\LogActivityRepository;
use Auth;
use App\Models\User;
use DB;
use App\Queries\PermissionQuery;

class LogActivityService extends Service
{

	protected $logActivityRepository;	
    protected $permissionQuery;

    public function __construct(    	
    	LogActivityRepository $LogActivityRepository,
        PermissionQuery $permissionQuery    	
    )
    {
    	$this->repository = $LogActivityRepository;    
        $this->permissionQuery = $permissionQuery;    
    }

    public function dxAddPermissionsByCompany($request, $organization_id){        
        $organization = DB::table('organizations as o')
            ->where('o.id', '=', Auth::user()->organization_id)
            ->first();
        $result = DB::table('organizations as o')
            ->where([['o.lft', '>', $organization->lft],['o.rgt','<',$organization->rgt]])
            ->select('o.title','o.id')
            ->get()->pluck('id')->all();       
        $organizations = [];
        foreach($result as $p){
            array_push($organizations,  ['organizations.id','=', $p], 'or');
        }
        unset($organizations[count($organizations)-1]);        
        $filter = json_decode($request['filter']);
        $permissions = [$organizations];
        if($filter != null){
            $filter =[$filter, 'and', $permissions];            
        }else{
            $filter =$permissions;            
        }
        $request['filter'] = json_encode($filter);      
        $request = $this->dxFilterCorrect($request);
        return $request;
    }    

    public function create($request)
    {
   	    $item = $this->repository->create($request);

        if ($item) {
            $this->repository->reindexModel($item, true);            
        }         

        return response()->json([
            'message'   => 'Добавлено'
        ], 200);
		
    }   

    public function delete($id){ 	
        
      	$model = $this->repository->findBy('phone', $id);                

        $this->repository->deleteFromIndex($model);            

        return $this->repository->delete($model->id);

	}	  

	protected function getSearchRepository()
    {
        return $this->repository;
    }    

	protected function addSearchConditions(User $user=null,array $filters=null)
    {
        return $filters;
    }

    public function getPermissionQuery(){
        return $this->permissionQuery;
    }

    public function getExportToExcelLib(){
        return null;
    }

}