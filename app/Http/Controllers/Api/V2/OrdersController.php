<?php
namespace App\Http\Controllers\Api\V2;

use App\Helpers\LogActivity;
use App\Models\Order;
use App\Models\Project;
use App\Models\ProjectGoal;
use App\Repositories\OrdersRepository;
use App\Repositories\UsersRepository;
use App\Repositories\OrganizationsRepository;
use App\Http\Requests\Api\V2\OrderUpdateRequest;
use App\Services\OrdersService;
use App\Services\GeoService;
use App\Services\UsersService;
use App\Services\UnloadsService;
use App\Services\RolesService;
use App\Services\AtsQueueService;
use App\Http\Requests\Api\V2\SearchRequest;
use App\Http\Requests\Api\V2\DxSearchRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Input;
use Response;
use Auth;
use DB;
use App\Models\History;

 
class OrdersController extends Controller
{
    protected $relations    = ['projects', 'sites'];

    public function getList(DxSearchRequest $request, OrdersService $service)
    {
        // return $request;
        // Валидация на подбор номера телефона
        if(!$this->cani("menu.main.orders.view_phone_number") && preg_match("/\bphones\b/i", $request['filter'])){

            preg_match("/(phones.+?).\\]/", $request['filter'], $matches);

            if(isset(json_decode('["'.$matches[1].'"]')[2]))
                $phone = json_decode('["'.$matches[1].'"]')[2];
            $first_char = $phone[0];
            $first_three_char = substr($phone, 0, 3);

            if(($first_char == 7 || $first_char == 8) && strlen($phone) != 11)
                return $this->errorResponse('Нет доступа', 403, ['order'=>'Нет доступа']);

            if($first_three_char == 374){
                if(strlen($phone) != 11)
                    return $this->errorResponse('Нет доступа', 403, ['order'=>'Нет доступа']);
                
                $numbers = [1, 2, 4, 5, 6, 9];
            }else{
                $numbers = [1, 2, 3, 4, 5, 6, 9];
            }

            if(in_array($first_char, $numbers) && strlen($phone) != 12)
                return $this->errorResponse('Нет доступа', 403, ['order'=>'Нет доступа']);
        }

        $user_id = null;
        if(!$this->userService->can("menu.main.orders.own_only", $this->auth->user()['organization_id']))
            $user_id = $this->auth->user()['id'];
        
        $request = $service->dxAddPermissions($request, $this->auth->user()['organization_id'], $user_id);

        if(isset($request['group'])){
            $request = $this->correctStatuses($request);
            $result =  $service->dxSwitchGroup($request);
            if(isset($result))
                return $result;
        }

        $permission_list = [];
        $permission_list["menu.main.orders.view_track_number"] =  $this->cani("menu.main.orders.view_track_number");
        $permission_list["menu.main.orders.view_phone_number"] =  $this->cani("menu.main.orders.view_phone_number");

        $list = $service->dxSearch($request, $permission_list);

        $info = [];
        if(!empty(Input::get('filter'))){
            $action = 'searchOrders';
            $url_params = Input::get('filter');
            $json_params = json_decode($url_params, true);

            foreach($json_params as $item){
                $info[] = $item;
            }
            $info = ['parameters' => $info];
        }
        else{
            $action = 'getOrders';
        }

        LogActivity::addToLog($action, $info);

        return response()
            ->json(['data' => $list, 'total' => $list->getTotal(), 'totalCount' => $list->getTotal()]);
    }

    private function correctStatuses($request){
        $selector = json_decode($request['group'])[0]->selector;
        if (explode("_", $selector)[0] =='status') {
            $matches = array();
            preg_match('~status_(\d+)~', $selector, $matches );
            $type = $matches[1];

            $request['group'] = str_replace($selector, 'statuses.name', $request['group']);

            $request['filter'] = str_replace($selector, 'statuses.name', $request['filter']);

            $filter = json_decode($request['filter']);
            if(gettype($filter)=='array'){
                $filter = [$filter[0], 'and', ['statuses.type','=',  $type]];
            }else{
                $filter = ['statuses.type','=',  $type];
            }
            $request['filter'] = json_encode($filter);
        }
        return $request;
    }

    public function exToExcel(DxSearchRequest $request, OrdersService $service)
    {
        $user_id = null;
        if(!$this->userService->can("menu.main.orders.own_only", $this->auth->user()['organization_id']))
            $user_id = $this->auth->user()['id'];
        
        $request = $service->dxAddPermissions($request, $this->auth->user()['organization_id'], $user_id);

        $permission_list = [];
        $permission_list["menu.main.orders.view_track_number"] =  $this->cani("menu.main.orders.view_track_number");
        $permission_list["menu.main.orders.view_phone_number"] =  $this->cani("menu.main.orders.view_phone_number");

        $info = [];
        if(!empty(request()->columns)){
            $json_params = json_decode(request()->columns, true);

            foreach($json_params as $item){
                $info[] = $item;
            }
        }

        LogActivity::addToLog('exportExcel', $info);

        return  response()->file($service->exToExcel($request, $permission_list));
    }

    public function salesReport(DxSearchRequest $request, OrdersService $service){

        $filters = json_decode($request['filter']);

        $filters_arra = [];
        $is_continue = false;
        
        if($filters && count($filters)>0){

            foreach($filters as $key=>$f){
                if($is_continue){
                    $is_continue = false;
                    continue;
                }
                if($f[0]=="report"){
                    $request['report'] = $f[2];
                    $is_continue=true;
                }
                if($f[0]=="section"){
                    $request['section'] = $f[2];
                    $is_continue=true;
                }
                if($f[0]!="report" && $f[0]!="section"){
                    $filters_arra[] = $f;
                }
            }
            $request['filter'] = json_encode($filters_arra);
        }

        $user_id = null;
        if(!$this->userService->can("menu.main.orders.own_only", $this->auth->user()['organization_id']))
            $user_id = $this->auth->user()['id'];

        $request = $service->dxAddPermissions($request, $this->auth->user()['organization_id'], $user_id);

        $list = $service->dxSearch($request);

        return response()
            ->json(['data' => $list, 'total' => $list->getTotal(), 'totalCount' => $list->getTotal()]);

    }

    public function salesReportDelates(DxSearchRequest $request, OrdersService $service){
        $list = $service->dxGroupedSearch($request);                 

        return response()
            ->json(['data' => $list, 'total' => $list->getTotal(), 'totalCount' => $list->getTotal()]);
    }


    public function getDoubles($key, OrdersService $service){
        return $service->get_doubles($key);
    }
    
    public function getByKey($key, OrdersRepository $ordersRepository, OrdersService $service, UsersService $usersService, UsersRepository $UsersRepository)
    {

        $check = $usersService->can('menu.main.orders.view', Auth::user()->organization_id);

        if(!$check)
            return $this->errorResponse('Нет доступа', 403, ['order'=>'Нет доступа']);

        $order = $ordersRepository->with($this->relations)->findBy('key', $key);

        if (is_null($order)) {
            return $this->errorResponse('Не найдено', 404, ['key'=>"Не найден заказ с ключём $key"]);
        }

        if($order->is_double == null){
            $service->add_existing_double($order);
        }

        $permission_list = [];
        $permission_list["menu.main.orders.view_phone_number"] =  $this->cani("menu.main.orders.view_phone_number");

        $order = $service->prepareOrder($order, $permission_list);

         \LogActivity::addToLog('openOrder', ['key'=>$key]);

        return $order;
    }


    public function changeManagerId($key, SearchRequest $request, OrdersService $service)
    {
        return ($service->changeManagerId($key, $request->all()))?$request->all():$this->errorResponse('Не изменено', 403, $request->all());        
    }


    public function create(OrderUpdateRequest $request, OrdersService $service)
    {

        $data = $request->validated();

        $return = $service->create_v2($data);        

        if(is_array($return))return $this->errorResponse(implode(", ", $return['order']), 422);    

        return $return;
    }

    public function checkDouble(OrdersRepository $ordersRepository)
    {
        exit;

        $orders = DB::table('orders')
            ->leftJoin('order_status', 'orders.id', '=', 'order_status.order_id')
            ->select('orders.id', 'orders.key', 'orders.import_id','orders.created_at', 'order_status.status_id', 'order_status.status_type')            
            ->where('orders.created_at','>=','2019-04-14')            
            ->orderBy('orders.id', 'asc')                      
            ->get()->toArray();

        $ignore = [7068,7061,7060,7057,7056,7055,7047,7046,7045,7044,7043,7042,7030,7029,7028,7027,7026,7025,6911,6897,6896,6895,6894,6893,6892,6786,6785,6773,6772,6771,6770,6769,6768,6680,6677,6671,6670,6669,6658,6657,6656,6655,6654,6653,6564,6561,6557,6556,6554,6553,6544,6543,6542,6541,6540,6539,6441,6440,6439,6438,6437,6436,6350,6349,6337,6336,6335,6334,6333,6332,6289,6275,6241,6240,6239,6238,6237,6236,6199,6194,6192,6172,6160,6159,6147,6146,6145,6144,6143,6142,6128,6084,6072,6071,6068,6067,6056,6055,6054,6053,6052,6051,6047,6022,6018,6017,5982,5966,5965,5964,5963,5962,5961,5941,5923,5922,5918,5916,5913,5912,5890,5878,5877,5876,5875,5874,5873,5866,5863,5862,5854,5846,5841,5836,5835,5808,5789,5788,5787,5786,5785,5784,5764,5750,5714,5713,5702,5701,5700,5699,5698,5697,5690,5672,5647,5632,5624,5615,5614,5613,5612,5611,5610,5559,5527,5526,5525,5524,5523,5522,5501,5476,5466,5455,5441,5440,5439,5438,5437,5436,5355,5354,5353,5352,5351,5350,5269,5267,5264,5262,5261,5260,5179,5178,5177,5176,5175,5174,5095,5094,5093,5092,5091,5090,5013,5012,5011,5010,5009,5008,4931,4930,4929,4928,4927,4926,4925,4849,4848,4847,4846,4845,4844,4843,4766,4765,4764,4763,4762,4761,4760,4685,4684,4683,4682,4681,4680,4679,4611,4610,4609,4608,4607,4606,4605,4539,4538,4537,4536,4535,4534,4533,4471,4470,4469,4468,4467,4466,4465,4410,4409,4408,4407,4406,4405,4404,4352,4351,4350,4349,4348,4347,4346,4295,4294,4293,4292,4291,4290,4289,4239,4238,4237,4236,4235,4234,4233,4189,4188,4187,4186,4185,4184,4183,4138,4137,4136,4135,4134,4133,4132,4088,4087,4086,4085,4084,4083,4082,4041,4040,4039,4038,4037,4036,4035,3998,3997,3996,3995,3994,3993,3992,3959,3958,3957,3956,3955,3954,3953,3920,3919,3918,3917,3916,3915,3914,3890,3881,3880,3879,3878,3877,3876,3875,3874,3871,3870,3868,3867,3862,3860,3858,3857,3852,3840,3839,3838,3837,3836,3835,3834,3833,3831,3830,3825,3817,3814,3813,3811,3802,3801,3800,3799,3798,3797,3796,3788,3779,3764,3763,3762,3761,3760,3759,3758,3755,3754,3753,3749,3745,3740,3736,3727,3726,3725,3724,3723,3722,3721,3718,3717,3704,3695,3694,3693,3692,3691,3690,3689,3685,3684,3665,3664,3663,3662,3661,3660,3659,3658,3657,3656,3655,3654,3653,3652,3650,3649,3646,3645,3644,3643,3642,3641,3640,3630,3629,3628,3627,3626,3625,3624,3621,3619,3618,3609,3608,3607,3606,3605,3595,3594,3593,3592,3590,3589];

        $temp = [];
        //$with_st = [];

        foreach ($orders as $order) {
            //if(($order->status_type !== 1) || ($order->status_type == 1 && $order->status_type !== null)) continue;
            $temp[$order->import_id][$order->id] = $order;
            //if($order->status_id>0)$with_st[$order->import_id][] = $order;
        }   

        //dd($temp);

        $delete=[];

        //$wait = [34,55,80,98,155,199,200,201,299,328,329,330,331];
        //$cancel = [59,60,61,62,63,99,100,101,156,158,160,161,163,183,332,345,346];

        foreach ($temp as $import_id => $doubles) {            
            $delete[$import_id] = [];
            $first = 0;
            $appr = false;
            $i = 1;
            foreach ($doubles as $double) {
                if(in_array($double->id, $ignore))continue;
                if($i==1)$first = $double->id;
                $i++;
                /* echo $i.' - '.$double->id.'<br>';
                if($appr == true)                {
                    $delete[$import_id][$double->id] = $double->id;
                      echo $i.' - 1'.'<br>';
                    continue;
                }
                if($double->status_id==17){                          
                    if(!isset($delete[$import_id][$first]) && $first!==$double->id) $delete[$import_id][$first] = $temp[$double->import_id][$first]->id;
                    $first = $double->id;       
                      echo $i.' - 2'.'<br>';
                    $appr = true;
                    continue;
                }
                if($double->status_id>0){
                    if(!isset($delete[$import_id][$first]) && $first!==$double->id) $delete[$import_id][$first] = $temp[$double->import_id][$first]->id;
                    $first = $double->id;
                      echo $i.' - 3'.'<br>';
                    continue;                    
                }

                echo $i.' - 4'.'<br>';*/
                
                if($first!=$double->id)$delete[$import_id][$double->id] = $double->id;
            }
        }

        /*foreach ($delete as $d_import_id => $d_doubles) {         
            
            foreach ($d_doubles as $d_double) {
                $order = Order::find($d_double);
                $order->statuses()->detach(); 
                $order->statuses()->attach(50,[
                    'user_id'=>1,
                    'status_type'=>1,
                    'created_at'=>Carbon::now()->format('Y-m-d H:i:s')
                ]);
                History::create([
                    'reference_table' => $ordersRepository->model(),
                    'reference_id'    => $order->id,
                    'actor_id'        => 1,
                    'body'            => json_encode(['statuses' => [1=>50]],JSON_UNESCAPED_UNICODE)
                ]);
                //$ordersRepository->reindexModel($order, true);
            }
        }*/

        
        dd($delete);



    }

    public function publicCreate(SearchRequest $request, OrdersService $service, UnloadsService $unloadsService, OrganizationsRepository $organizationsRepository){
        
        $all = $request->all();                     
        $api_key = $all['api_key'];        

        if(empty($api_key))return $this->errorResponse('Неверный апи-ключ', 401);    

        if(strlen($api_key) != 32)return $this->errorResponse('Неверный апи-ключ', 401);   

        $organization = $organizationsRepository->findWhere([
            ['api_key', $api_key],
            ['is_company',true]
        ])->first();

        if(!$organization){
            return $this->errorResponse('Неверный апи-ключ', 401);    
        }                        

        $data = json_decode($all['data'], true);        

        try {
            
            $result = $unloadsService->setOrdersFromAPI($data, $organization->id)->key;   
                                 
            if(!empty($result)){                                        
                return ["message"=>"Заказ успешно добавлен", "key" => $result];
            }else{
                return $this->errorResponse('Ошибка заказа', 404);    
            }
        }catch(\Throwable $e){                    
            return $this->errorResponse('Ошибка заказа', 404);
        }
    }

    public function publicStatus(SearchRequest $request, UnloadsService $unloadsService){

        $all = $request->all();                     

        if(!isset($all['api_key']))return $this->errorResponse('Неверный апи-ключ', 401);    

        $api_key = $all['api_key'];        

        if(empty($api_key))return $this->errorResponse('Неверный апи-ключ', 401);    

        if(strlen($api_key) != 32)return $this->errorResponse('Неверный апи-ключ', 401);   

        try {
            $data = explode(",", $all['data']);

            $take = count($data);

            $add_filter = [];
            foreach ($data as $index => $order_key) {            
                $add_filter[] = ["key", "=", $order_key];
                if($index != ($take-1))$add_filter[] = "or";
            }        
            
            $orders = $unloadsService->getOrdersByAPIKey($api_key, 0, $take, $add_filter);

            if($orders===false)return response(json_encode([]), 200, ['Content-Type' => 'application/json']);      

            return response(json_encode($orders), 400, ['Content-Type' => 'application/json']);      
        }catch(\Throwable $e){                    
            return $this->errorResponse('Ошибка', 404);
        }
    }

    public function getAtsQueues(SearchRequest $request, OrdersService $service, UnloadsService $unloadsService){        

        $all = $request->all();  

        if(!isset($all['api_key']))return $this->errorResponse('Неверный апи-ключ', 401);    

        $api_key = $all['api_key'];            

        if(empty($api_key))return $this->errorResponse('Неверный апи-ключ', 401);    

        if(strlen($api_key) != 32)return $this->errorResponse('Неверный апи-ключ', 401);  

        $ats_queue = AtsQueueService::getAtsQueueByUnloadsKey($api_key);    

        if(empty($ats_queue))return $this->errorResponse('Не найдена очередь', 401);                    
        
        try {
            $orders = $service->getOrdersByQueue($ats_queue->toArray(), $api_key, $unloadsService);
            
            //Ответ
            return response()
                ->json(["result"=>true, "massage"=>"action completed", "content"=> $orders]);
        }catch(\Throwable $e){                    
            return $this->errorResponse('Ошибка', 404);
        }
        
    }

    public function update($id, OrderUpdateRequest $request, OrdersService $service, UsersService $usersService)
    {
        $check = $usersService->can('menu.main.orders.edit', Auth::user()->organization_id);

        if(!$check)
           return $this->errorResponse('Нет доступа', 403, ['order'=>'Нет доступа. Данные не были изменены.']);

        $data   = $request->validated();

        $permission_list = [];
        $permission_list["menu.main.orders.view_phone_number"] =  $this->cani("menu.main.orders.view_phone_number");

        $order  = $service->updateBy($id, $data, "key", $permission_list);

        if(is_array($order))
            return $this->errorResponse('Ошибка валидации', 403, $order);

        if($order) {
            $order->load($this->relations);
        }

        return $order;
    }

    public function delete($id)
    {
        return $id;
    }
    
    public function getByOperator($operator_id, OrdersService $service)
    {
        $result = $service->getByOperator($operator_id);
        if ($service->errors()) {
            return $this->error($service->getError());
        }
        return response()->json(['data' => $result]);
    }

    public function refuseOrder($id, OrdersService $service){
        $data = $service->refuseOrder($id);
        return response()->json(["message"=> $data['message']], $data['status']);
    }
}
