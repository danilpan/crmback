<?php
namespace App\Http\Controllers\Api\V2;

use App\Repositories\LogActivityRepository;
use App\Http\Requests\Api\V2\SearchRequest;
use App\Http\Requests\Api\V2\AddToLogRequest;
use App\Services\LogActivityService;
use Elasticsearch\Client as ElasticClient;
use DB;
use Auth;

class LogActivitiesController extends Controller
{
    
    public function getList(SearchRequest $request, LogActivityService $service, ElasticClient $elastic)
    {       

		$permission_list = [];
        $permission_list["menu.main.logs.users_visits.view"] =  $this->cani("menu.main.logs.users_visits.view");
        $permission_list["menu.main.logs.users_visits.child_company_users_view"] =  $this->cani("menu.main.logs.users_visits.child_company_users_view");                    

        $list = [];

        if($permission_list["menu.main.logs.users_visits.view"]){       	
			if($permission_list["menu.main.logs.users_visits.child_company_users_view"]){        		
        		$request = $service->dxAddPermissions($request, Auth::user()->organization_id);
        	}else{
        		$request = $service->dxAddPermissionsByCompany($request, Auth::user()->organization_id);
        	}
        	$list = $service->dxSearch($request);                 
        }              

        return response()
            ->json(['data' => $list, 'total' => $list->getTotal(), 'totalCount' => $list->getTotal()]);
    }

    public function addToLog(AddToLogRequest $request){
        return \LogActivity::addToLog($request->get('action'),["info"=>$request->get('info')]);
    }
    
}
