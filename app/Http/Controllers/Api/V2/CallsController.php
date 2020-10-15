<?php
namespace App\Http\Controllers\Api\V2;

//use App\Http\Requests\Api\V2\ProjectCreateRequest;
//use App\Http\Requests\Api\V2\ProjectUpdateRequest;
use App\Helpers\LogActivity;
use App\Models\Order;
use App\Repositories\CallsRepository;
use App\Http\Requests\Api\V2\SearchRequest;
use App\Http\Requests\Api\V2\CallDoRequest;
use App\Http\Requests\Api\V2\DxSearchRequest;
use App\Services\AtsService;
use Auth;

use App\Services\CallsService;

class CallsController extends Controller
{
    public function getList(SearchRequest $request, CallsService $service)
    {
        
        /*
            $query          = $request->get('q');
            $page           = $request->get('page', 1);
            $perPage        = $request->get('per_page', 20);
            $sortKey        = $request->get('sort_key', 'id');
            $sortDirection  = $request->get('sort_direction', 'asc');
            $filters        = $request->get('filters');
        */

        //   79069034347

        // $filters    = [
            /*'queue_id'  => [
                'terms' => ['233']
            ],*/
            // 'phone'  => [
            //     'prefix' => '79',
            // ],
            // 'call_type'  => [
            //     'terms' => ['out']
            // ],
            // 'step_id'  => [
            //     'to' => 6,
            // ],
        // ];

        //dd(http_build_query(['filters'=>$filters]));

       // $calls = $callsRepository->search($page, $perPage, $sortKey, $sortDirection, $filters, $query);
       // return $calls;
       
        //$request = $service->dxAddPermissions($request, $this->auth->user()['organization_id']);

        if(isset($request['group'])){
            $result =  $service->dxSwitchGroup($request);
            if(isset($result))
                return $result;
        }   

        return  $service->dxSearch($request);
    }

    public function export(DxSearchRequest $request, CallsService $service)
    {
        //$request = $service->dxAddPermissions($request, $this->auth->user()['organization_id']);
        return  response()->file($service->exToExcel($request)); 
    }

    public function doCall(CallDoRequest $request, CallsService $service)
    {

        $sip = $this->auth->user()['phone_office'];
        return $service->doCall($request->validated(), $sip);
    }

    public function addCall(SearchRequest $request, CallsService $service)
    {        

        $data = $request->all();

        if(empty($data['api_key']))return $this->errorResponse('Неверный апи-ключ', 401);    

        if(!AtsService::getByKey($data['api_key']))return $this->errorResponse('Неверный апи-ключ', 401);   
        
        try {
            $info = $service->getInfoByType($data);                  
            if(!empty($info)){                                        
                return ["message"=>"Звонок успешно добавлен"];
            }else{
                return $this->errorResponse('Ошибка добавления', 404);    
            }
        }catch(\Throwable $e){                    
            return $this->errorResponse('Ошибка добавления', 404);
        }        
    }

    public function addCallStatus(SearchRequest $request, CallsService $service)
    {        

        $data = $request->all();        

        if(empty($data['api_key']))return $this->errorResponse('Неверный апи-ключ', 401);    

        if(!AtsService::getByKey($data['api_key']))return $this->errorResponse('Неверный апи-ключ', 401); 

        $calls = json_decode(base64_decode(trim(htmlspecialchars($data['calls']))),true);               

        //$calls = $data['calls'];               
        
        try {            
            
            $info = $service->addCallStatus($calls);                  

            if(!empty($info)){                                        
                return $info;
            }else{
                return $this->errorResponse('Не найдено заказов для обновления', 404);    
            }
        }catch(\Throwable $e){                    
            return $this->errorResponse($e->getMessage(), 404);
        }        
    }

    public function publicGetList(SearchRequest $request, CallsRepository $repository)
    {

        $all = $request->all();   
            
        if(!isset($all['api_key']))return $this->errorResponse('Неверный апи-ключ', 401);            

        if($all['api_key'] != '3FMaSceNxULEPfUZ9sYkmrac4zZEWKLu')return $this->errorResponse('Неверный апи-ключ', 401);   

        $data = explode(",", $all['data']);       

        try {
            $filters['constant_score']['filter']['bool']['must'][]['terms']['order_key'] = $data;        
            $calls = $repository->searchByParams($filters,['order_key'=>'asc','time'=>'asc'],1,10000);                    
            return response(json_encode($calls), 200, ['Content-Type' => 'application/json']);                  
        }catch(\Throwable $e){                    
            return $this->errorResponse('Ошибка', 404);
        }       
        
    }

    /*public function getSuggest(SuggestRequest $request, ProjectsService $service)
    {
        return $this->suggest($request, $service);
    }


    public function getById($id, ProjectsRepository $projectsRepository)
    {
        $projects = $projectsRepository->find($id);

        return $projects;
    }

    public function create(ProjectCreateRequest $request, ProjectsRepository $repository)
    {
        $data = $request->validated();
        $project = $repository->create($data);

        return $project;
    }

    public function update($id, ProjectUpdateRequest $request, ProjectsService $projectsService)
    {
        $project = $projectsService->update($id, $request->validated(), true);

        return $project;
    }

    public function delete($id, ProjectsRepository $repository)
    {
        return $repository->delete($id);
    }*/
    
    public function goOperatorCall($order_id, $phone, CallsService $service)
    {
        $order_key = Order::find($order_id)->key;
        LogActivity::addToLog('goPhone', ['key' => $order_key]);

        $result = $service->goOperatorCall($order_id, $phone);
        if ($service->errors()) {
            return $this->error($service->getError());
        }

        return response()->json(['data' => $result]);
    }
}
