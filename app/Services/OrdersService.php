<?php
namespace App\Services;

use App\Models\Call;
use App\Models\Order;
use App\Repositories\OrdersRepository;
use App\Repositories\StatusRepository;
use App\Repositories\GasketRepository;
use App\Repositories\ProjectsRepository;
use App\Repositories\BlackListRepository;
use App\Repositories\ProjectPageRepository;
use App\Repositories\ProjectGoalRepository;
use App\Repositories\OrganizationsRepository;
use App\Repositories\SiteRepository;
use App\Repositories\StatusesRepository;
use App\Repositories\DeliveryTypesRepository;
use App\Repositories\LnkRoleStatusRepository;
use App\Repositories\UsersRepository;
use App\Repositories\CallsRepository;
use App\Services\ProjectGoalService;
use App\Services\ProjectGoalScriptService;
use App\Services\UnloadsService;
use App\Models\Comment;
use App\Models\Sale;
use App\Models\Project;
use App\Models\ProjectPage;
use App\Models\ProjectGoalScript;
use App\Models\ProjectGasket;  
use App\Models\ProjectGoal;
use App\Models\History;
use App\Models\OrdersDialSteps;
use RuntimeException;
use Carbon\Carbon;
use DB;
use Auth;
use App\Models\User;
use App\Models\AtsUser;
use App\Models\Ats;
use App\Models\CallStatus;
use App\Models\OrderImportIds;

use App\Queries\PermissionQuery;

use App\Libraries\ExportToExcel;


class OrdersService extends Service
{
    private $flags = [];     

    protected $ordersRepository;
    protected $lnkRoleStatusRepository;
    protected $gasketRepository;
    protected $projectRepository;
    protected $siteRepository;
    protected $statusesRepository;
    protected $ProjectPageRepository;
    protected $ProjectGoalRepository;
    protected $callsRepository;
    protected $ProjectGoalService;
    protected $ProjectGoalScriptService;
    protected $BlackListRepository;
    protected $DeliveryTypesRepository;    
    protected $UsersService;
    protected $organizationsService;
    protected $organizationsRepository;
    protected $rolesService;
    protected $geoService;
    protected $statusesService;
    protected $permissionQuery;
    protected $exportToExcel;
    protected $usersRepository;

    public function __construct(
        OrdersRepository $ordersRepository,
        GasketRepository $gasketRepository,
        ProjectsRepository $projectRepository,
        LnkRoleStatusRepository $lnkRoleStatusRepository, 
        SiteRepository $siteRepository,
        StatusesRepository $statusesRepository,
        ProjectPageRepository $ProjectPageRepository,
        ProjectGoalRepository $ProjectGoalRepository,
        CallsRepository $callsRepository,
        ProjectGoalService $ProjectGoalService,
        ProjectGoalScriptService $ProjectGoalScriptService,
        BlackListRepository $BlackListRepository,
        DeliveryTypesRepository $DeliveryTypesRepository,
        UsersRepository $usersRepository,        
        UsersService $UsersService,
        OrganizationsService $organizationsService,
        RolesService $rolesService,
        OrganizationsRepository $organizationsRepository,
        GeoService $geoService,
        StatusesService $statusesService,
        PermissionQuery $permissionQuery,
        ExportToExcel $exportToExcel
    ) {
        $this->ordersRepository = $ordersRepository;
        $this->lnkRoleStatusRepository = $lnkRoleStatusRepository;
        $this->gasketRepository = $gasketRepository;
        $this->projectRepository = $projectRepository;
        $this->siteRepository = $siteRepository;
        $this->statusesRepository = $statusesRepository;
        $this->ProjectPageRepository = $ProjectPageRepository;
        $this->ProjectGoalRepository = $ProjectGoalRepository;
        $this->callsRepository = $callsRepository;
        $this->ProjectGoalService = $ProjectGoalService;
        $this->ProjectGoalScriptService = $ProjectGoalScriptService;
        $this->BlackListRepository = $BlackListRepository;
        $this->DeliveryTypesRepository = $DeliveryTypesRepository;
        $this->UsersRepository = $usersRepository;        
        $this->UsersService = $UsersService;
        $this->organizationsService = $organizationsService;
        $this->organizationsRepository = $organizationsRepository;
        $this->rolesService = $rolesService;
        $this->geoService = $geoService;
        $this->statusesService = $statusesService;
        $this->permissionQuery = $permissionQuery;
        $this->exportToExcel = $exportToExcel;
    }


/*    public function updatePhones($order=null){
        if($order==null)
            $order =  Order::find(488);

        if(isset($order->phones) && count($order->phones)>0){
            $phones = [];
            foreach ($order->phones as $key=>$value) {
                if(strlen($value)>4)
                    $phones[] = substr($value, 0, -5) . 'xxxxx';
            }
            $order->phones = $phones;
            $order->save();
        }

        $order = $order->next();        
        if($order != null)
            $this->updatePhones($order);
    }*/

    public function create(array $orderData, array $gasketData = null, array $projectData = null, array $siteData = null)
    {
        DB::transaction(function() use ($orderData, $gasketData, $projectData, $siteData){
            $project    = $this->prepareModel($projectData, $this->projectRepository);
            $site       = $this->prepareModel($siteData, $this->siteRepository);
            $gasket     = $this->gasketRepository->create($gasketData);

            $order      = $this->prepareModel($orderData, $this->ordersRepository);
            $update     = [
                'site_id'       => $site->id,
                'gasket_id'     => $gasket->id,
                'project_id'    => $project->id
            ];

            if(isset($order->phones) && $this->searchInBlackList($order->phones)){
                $order->statuses()->attach(52,[
                    'user_id'=>Auth::user()->id,
                    'status_type'=>1,
                    'created_at'=>Carbon::now()->format('Y-m-d H:i:s')
                ]);
            };

            if(!$order->key_lead) {
                $update['key_lead'] = md5($order->id);
            }

            $this->ordersRepository->update($update, $order->id);
        });
    }

    public function create_v2(array $orderData)
    {

        return DB::transaction(function() use ($orderData){

            if(!isset($orderData['type']))$orderData['type'] = 'operator';
            if(isset($orderData['phones']) && isset($orderData['phones'][0])){
                $orderData['phones'][0] = $this->normalizePhone($orderData['phones'][0]);
                $orderData['time_zone'] = $this->geoService->getTimeZoneByPhone($orderData['phones'][0]);
            }

            $organization_id = null;
            $geo_data = [];

            if(isset(Auth::user()->organization_id)){
                $organization_id = Auth::user()->organization_id;
            }else{
                $organization_id = $orderData['organization_id'];
            }

            if(isset($orderData['phones']) && isset($orderData['phones'][0])){          
                $geo_data = $this->geoService->getByPhone($orderData['phones'][0]);            
                if(!empty($geo_data) && !isset($orderData['country_code']))$orderData['country_code'] = $geo_data['code']; 
            }

            if(isset($orderData['client_name'])){
                $sex_id = $this->defineSex($orderData['client_name']);
                $orderData['sex_id'] = $sex_id;    
            }
            
            $orderData['organization_id'] = $organization_id;
            $orderData['is_unload'] = 1; 
            
            $order = $this->ordersRepository->create($orderData);            

            if(isset($orderData['comment']) && !empty($orderData['comment'])){
                $this->add_comment($order, $orderData['comment']);
            }

            if($orderData['type']!='phone'){
                if(isset($orderData['project_info']) && !empty($orderData['project_info'])){
                    $this->add_project_info($order, $orderData['project_info']);                  
                }
            }     

            if($orderData['type']=='phone'){
                if(isset($orderData['project_info']) && !empty($orderData['project_info'])){
                    $this->add_several_project_info($order, $orderData['project_info']);        
                }          
            }

            if(isset($orderData['statuses']))
                $this->add_statuses($order, $orderData, 'new');        

            if(isset($orderData['import_id']))
                $this->add_import_ids($order, $orderData['import_id']);

            if(!$order->key) {
                $update['key'] = $this->get_key($order->id);
            }
 
            $temp_project_arr = $order->projects->toArray();

            if($order->projects->count() && isset($orderData['goal']) && !empty($orderData['goal'])){
                $goal_info = $this->check_goal($orderData, $temp_project_arr[0]['id']);
            }elseif($order->projects->count() && $geo_data){
                $goal_info = $this->ProjectGoalService->getGoalId($geo_data['id'], $temp_project_arr[0]['id']);
            }

            if(!empty($goal_info)){
                $update['project_goal_id'] = $goal_info['id'];
                $update['project_goal_script_id'] = $this->ProjectGoalScriptService->getScriptIdMinViews($goal_info['id']);
                if(!empty($update['project_goal_script_id']))$this->ProjectGoalScriptService->addScriptViews($update['project_goal_script_id']);

                $update['profit'] = (isset($orderData['profit']))?$orderData['profit']:$goal_info['web_master_payment'];                
                $update['real_profit'] = (isset($orderData['real_profit']))?$orderData['profit']:$goal_info['web_master_payment'];    
            }         

            if(isset($orderData['sales']) && !empty($orderData['sales'])){
                $this->add_sales($order, $orderData['sales']);
            }

            if(isset($orderData['phones'][0]) && !empty($orderData['phones'][0]))
                $this->add_double($orderData, $order);

            $order = $this->ordersRepository->update($update, $order->id);            

            if ($order) {               
                if(isset($this->flags['project']) && $this->flags['project']){                    
                    $projects = $order->projects()->get();
                    if($projects->count() > 0)
                    foreach ($projects as $project) {
                        $this->projectRepository->reindexModel($project, true);
                    }
                }
                if(isset($this->flags['project_page']) && $this->flags['project_page']){                    
                    $project_pages = $order->project_pages()->get();
                    if($project_pages->count() > 0)
                    foreach ($project_pages as $project_page) {
                        $this->ProjectPageRepository->reindexModel($project_page, true);  
                    }
                }
                $this->ordersRepository->reindexModel($order, true);
            }

            return $order;
        });
    }

    public function update($id, array $orderData = null)
    {


        DB::transaction(function() use ($id, $orderData){
            $this->ordersRepository->update($orderData, $id);
        });

        $order  = $this->ordersRepository->find($id);

        return $order;
    }

    public function updateBy($id, array $orderData = null, $attribute = "id", $permission_list = null, $reindex = true)
    {

        // dd("updateBy", $orderData);
        //if(!isset($orderData['type']))$orderData['type'] = 'operator';       

        $file_name = date('Y-m-d').'.txt';
        $strD = '======== '.date('Y-m-d H:i:s')." =========\n";
        $strD .= $attribute."\n";
        $strD .= $id."\n";
        $strD .= (isset(Auth::user()->id)?Auth::user()->id:1)."\n";
        $strD .= (json_encode($orderData))."\n";                        

        $dir = base_path('storage/app/files/order_updates');
        if (!file_exists($dir) && !is_dir($dir)) {
            if(!mkdir($dir, 0755, true)) {
                return ['logs'=>"Не удалось создать каталог $dir"];
            }
        }
        
        file_put_contents("$dir/$file_name", $strD, FILE_APPEND | LOCK_EX);

        if(!isset($orderData['country_code']) && isset($orderData['phones']) && $orderData['phones'][0]){                                   
            $geo_data = $this->geoService->getByPhone($orderData['phones'][0]);            
            if(!empty($geo_data))$orderData['country_code'] = $geo_data['code']; 
        }            

        //manager_id ставится только при изменении статусов 1 типа
        if(isset($orderData['manager_id']) && isset(Auth::user()->id))unset($orderData['manager_id']);        

        if(isset($orderData['phones']) && isset($orderData['phones'][0])){            
            $orderData['time_zone'] = $this->geoService->getTimeZoneByPhone($orderData['phones'][0]);
            if($permission_list != null && !$permission_list["menu.main.orders.view_phone_number"]){
                $check_order_info = $this->ordersRepository->searchByParams(
                    ['match' => [
                        $attribute => $id                ]
                    ], 
                    [$attribute=>'asc']
                )->toArray();  
                if(strpos($orderData['phones'][0], "?") || strpos($orderData['phones'][0], "*")){
                    if(isset($orderData['phones'][1])){
                        if(strpos($orderData['phones'][1], "?") || strpos($orderData['phones'][1], "*")){
                            unset($orderData['phones']);                        
                        }else{                        
                            if(!isset($check_order_info[0]['phones'][1])){
                                $orderData['phones'][0] = $check_order_info[0]['phones'][0];      
                            }else{
                                unset($orderData['phones']);                                
                            }
                        }
                    }else{
                        unset($orderData['phones']);                        
                    }                
                }else{
                    if($orderData['phones'][0] != $check_order_info[0]['phones'][0])unset($orderData['phones']);
                }
            }
        }        

        DB::transaction(function() use ($id, $orderData, $attribute){
            $change = $this->ordersRepository->updateBy($orderData, $id, $attribute);
            if($change->getChanges()){
                $this->update_dial_step($change->id, $change->getChanges());           
            }            
        });

        $order = $this->ordersRepository->findBy($attribute, $id);        

        $update = [];


        if(isset($orderData['project_info']) && !empty($orderData['project_info'])){
            $this->add_project_info($order, $orderData['project_info']);
            $temp_project_arr = $order->projects->toArray();
            $goal_info = $this->ProjectGoalService->getGoalId($geo_data['id'], $temp_project_arr[0]['id']);
            if(!empty($goal_info)){
                $update['project_goal_id'] = $goal_info['id'];
                $update['project_goal_script_id'] = $this->ProjectGoalScriptService->getScriptIdMinViews($goal_info['id']);
                if(!empty($update['project_goal_script_id']))$this->ProjectGoalScriptService->addScriptViews($update['project_goal_script_id']);

                //$update['profit'] = $goal_info['web_master_payment'];                
                //$update['real_profit'] = $goal_info['web_master_payment'];    
            }
            if(!empty($order->project_goal_id) && empty($goal_info)){
                $update = ["project_goal_id"=>null,"project_goal_script_id"=>null,"profit"=>null,"real_profit"=>null];
            }

            $this->ordersRepository->update($update, $order->id);
        }

        if(isset($orderData['comment']) && !empty($orderData['comment'])){
            $this->add_comment($order, $orderData['comment']);
        }

        if(isset($orderData['statuses']['1']) &&  $orderData['statuses']['1'] ==17 && !empty($this->validate_order($order, $orderData))  && isset(Auth::user()->id)) {
            return [
                    'order' => $this->validate_order($order, $orderData)
            ];
        }elseif(isset($orderData['sales']) && !empty($orderData['sales'])){
            $this->add_sales($order, $orderData['sales']);
        }

        if(isset($orderData['statuses']) && !empty($orderData['statuses'])){
            $this->add_statuses($order, $orderData);
        }

        if(isset($orderData['import_id']))
            $this->add_import_ids($order, $orderData['import_id']);

        if(isset($orderData['phones'][0]) && !empty($orderData['phones'][0]))
            $this->add_double($orderData, $order);

        if(isset($orderData['dial_step_info']))
            $this->add_dial_step($order, $orderData['dial_step_info']);                     

        $new_order = $this->ordersRepository->findBy($attribute, $id);

        if ($new_order && $reindex) {
           $this->ordersRepository->reindexModel($new_order, true);
        }

        return $this->prepareOrder($new_order, $permission_list);
    }

    public function add_import_ids($order, $import_id){
        $order_import_ids = OrderImportIds::where('order_id', $order->id)->get();
        if(!$order_import_ids->contains('import_id', $import_id)){
            $order_import_id = new OrderImportIds;
            $order_import_id->order_id = $order->id;
            $order_import_id->import_id = $import_id;
            $order_import_id->save();
        }
    }

    public function add_existing_double($order){
        if (empty($order['phones'])){
            return ;
        }

        $order_update['is_double'] = 0;
        $project_id = 0;
        $num = $order['phones'][0];

        // $order->statuses()->detach([50,57]);     //сброс дубль статусов при обновлении

        if($order->projects()->count() == 1){
            $project_id = $order->projects()->first()->id;
        }elseif(isset($order['project_info']['project_id'])){
            $project_id = $order['project_info']['project_id'];
        }
        $phone_orders = Order::where('orders.phones', 'like', "["."\"$num\""."]")
            ->orWhere('orders.phones', 'like', "["."\"$num\"".",%")->get();
        $project_phones_orders = null;

        if($project_id != 0){
            $project_phones_orders = DB::table('order_project as op')
                ->where('op.project_id', $project_id)
                ->join('orders as o', function ($jin) use ($num) {
                    $jin->on('o.id', '=', 'op.order_id')
                        ->where('o.phones', 'like', "[" . "\"$num\"" . "]")
                        ->orWhere('o.phones', 'like', "[" . "\"$num\"" . ",%");
                })
                ->join('order_status as os', function ($jin) {
                    $jin->on('os.order_id', '=', 'o.id')
                        ->where('os.status_id', 17);
                })
                ->select('o.created_at')
                ->orderBy('o.created_at');
        }

        if($phone_orders->count()>1) {
            $order_update['is_double'] = 1;
            $is_have_statuses_1_type = $order->statuses()->where(['status_type' => 1])->count();

            if ($is_have_statuses_1_type == 0){
                if ($project_phones_orders != null && $project_phones_orders->count() > 0) {
                    $order_date = $order->created_at;
                    $first_project_order_date = new Carbon($project_phones_orders->first()->created_at);
                    $interval = abs($first_project_order_date->diffInHours($order_date));
                    if ($interval < 48) {
                        $order->statuses()->attach(57, [      // Просит в сервисный центр
                            'user_id' => 1,
                            'status_type' => 1,
                            'created_at' => Carbon::now()->format('Y-m-d H:i:s')
                        ]);
                    } else {
                        $order->statuses()->attach(50, [      // Дубль
                            'user_id' => 1,
                            'status_type' => 1,
                            'created_at' => Carbon::now()->format('Y-m-d H:i:s')
                        ]);
                    }
                }
            }
        }
        if(isset($order_update['dial_time']))
            unset($order_update['dial_time']);
        $order->is_double = $order_update['is_double'];
        $this->ordersRepository->update($order_update, $order->id);
    }

    public function add_double($orderData, $order){
        $order_update['is_double'] = 0;
        $project_id = 0;
        $num = $orderData['phones'][0];

        // $order->statuses()->detach([50,57]);     //сброс дубль статусов при обновлении

        if($order->projects()->count() == 1){
            $project_id = $order->projects()->first()->id;
        }elseif(isset($orderData['project_info']['project_id'])){
            $project_id = $orderData['project_info']['project_id'];
        }
        $phone_orders = Order::where('orders.phones', 'like', "["."\"$num\""."]")
            ->orWhere('orders.phones', 'like', "["."\"$num\"".",%")->get();
        $project_phones_orders = null;

        if($project_id != 0){
            $project_phones_orders = DB::table('order_project as op')
                ->where('op.project_id', $project_id)
                ->join('orders as o', function ($jin) use ($num) {
                    $jin->on('o.id', '=', 'op.order_id')
                        ->where('o.phones', 'like', "[" . "\"$num\"" . "]")
                        ->orWhere('o.phones', 'like', "[" . "\"$num\"" . ",%");
                })
                ->join('order_status as os', function ($jin) {
                    $jin->on('os.order_id', '=', 'o.id')
                        ->where('os.status_id', 17);
                })
                ->select('o.created_at')
                ->orderBy('o.created_at');
        }

        if($phone_orders->count()>1){
            $order_update['is_double'] = 1;
            // -> Есть ли статусы 1 группы
            $is_have_statuses_1_type = $order->statuses()->where(['status_type'=>1])->count();
            
            if($is_have_statuses_1_type == 0)
            if ($project_phones_orders != null && $project_phones_orders->count() > 0) {
                $order_date = $order->created_at;
                $first_project_order_date = new Carbon($project_phones_orders->first()->created_at);
                $interval = abs($first_project_order_date->diffInHours($order_date));
                if($interval < 48){
                    $order->statuses()->attach(57, [      // Просит в сервисный центр
                        'user_id' => 1,
                        'status_type' => 1,
                        'created_at' => Carbon::now()->format('Y-m-d H:i:s')
                    ]);
                }else{
                    $order->statuses()->attach(50, [      // Дубль
                        'user_id' => 1,
                        'status_type' => 1,
                        'created_at' => Carbon::now()->format('Y-m-d H:i:s')
                    ]);
                }
            }
        }
        $this->ordersRepository->update($order_update, $order->id);

    }

    public function get_doubles($key){
        $order = Order::where('key',$key)->first();
        if (!empty($order->phones[0]))
            $num = $order->phones[0];
        else return [];
        $orders = Order::where('phones', 'like', "["."\"$num\""."]")
            ->orWhere('phones', 'like', "["."\"$num\"".",%")->orderBy("created_at")->get();

        $data = [];

        if(!empty($orders) && count($orders)>1){
            foreach($orders as $item){
                $order_double = [];
                $order_double['project'] = '';
                $order_double['status_gr_1'] = $order_double['status_gr_3'] = $order_double['status_gr_5'] = '';
                $order_double['geo'] = '';
                $order_double['key'] = $item->key;

                if($item->projects()->first()) {
                    $order_double['project'] = $item->projects()->first()->title;
                }

                $order_double['fio'] = $item->client_name;
                $order_double['date'] = $item->created_at;

                if($item->statuses()->first()){
                    $order_double['status_gr_1'] = $item->statuses()->get()->where('type', '1')->first()['name'];
                    $order_double['status_gr_3'] = $item->statuses()->get()->where('type', '3')->first()['name'];
                    $order_double['status_gr_5'] = $item->statuses()->get()->where('type', '5')->first()['name'];
                }
                if($item->geo()->first()) {
                    $order_double['geo'] = $item->geo()->first()->name_ru;
                }else{
                    $order_double['geo'] = $item->phone_country;
                }

                $data[] = $order_double;
            }
        }

        return $data;
    }

    public function add_comment($order, $comment_data){

        $user_id = null;
        if(isset( Auth::user()->id)){
            $user_id = Auth::user()->id;
        }else{
            $user_id = $order['manager_id'];
        }

        if(is_array($comment_data)){
            // При загрузке через API
            foreach($comment_data as $cd){
                $comment = $order->comments()->find($cd['id']);
                if($comment == null){
                    $comment = new Comment();
                    $comment->text = $cd['content'];
                    $comment->user_id = $user_id;
                    $comment->organization_id = $order->organization_id;
                    $order->comments()->save($comment);
                }
            }
        }else{
            // При добавлении комментариев через веб-интерфейс
            $comment = new Comment();
            $comment->text = $comment_data;
            $comment->user_id = $user_id;
            $comment->organization_id = $order->organization_id;
            $order->comments()->save($comment);
        }
    }

    public function add_dial_step($order, $data){        
        if($order->dial_time > $data['dial_time']){
            $set_date = $order->dial_time->format('Y-m-d H:i:s');
        }else{
            $set_date = $data['dial_time'];
        }
        if(OrdersDialSteps::updateOrCreate(
            ['queue_id' => $data['queue_id'], 'order_id' => $order->id],
            ['dial_step' => $data['dial_step'], 'dial_time' => $set_date]
        )){
            $order['dial_time'] = $set_date;    
            $order->save();
        };                    
    }

    public function update_dial_step($id, $changes){ // BUG: метод обновляет шаги не проверяя очередь, возможно баг
        if(isset($changes['dial_time'])){            
            DB::table('orders_dial_steps')
                ->where('order_id', $id)
                ->update(['dial_time' => $changes['dial_time']]);        
        }
    }

    public function add_several_project_info($order, $project_info){
        $project_arr = [];
        $project_page_arr = [];
        foreach ($project_info as $info) {            
            $project_arr[]=$info['project_id'];
            $project_page_arr[]=$info['page_id'];
        }
        $project_temp['project_result'] = $order->projects()->sync($project_arr);
        $project_temp['page_result'] = $order->project_pages()->sync($project_page_arr);
        $project_temp['order_id'] = $order->id;     
        $this->projectHistory($project_temp, $order);
    }

    public function add_project_info($order, $project_info){
        $project_temp = [];
        $project_id = 0;

        if(isset($project_info['project_page'])){
            $project = null;
            // При загрузе через API. Если нет Проекта в базе, то создаем его. Начало

                if(isset($project_info['id']))$project =  Project::find($project_info['id']); 
                if($project==null && isset($project_info['import_id'])){
                    $project =  Project::where('import_id', $project_info['import_id'])->first(); 
                    if($project != null)
                        $project_info['id'] = $project->id;
                }

                if($project==null && isset($project_info['import_id']) && !empty($project_info['import_id'])){
                    $project = new Project();
                    $project->title = $project_info['name'];
                    $project->import_id = $project_info['import_id'];
                    $project->name_for_client = $project_info['name_for_client'];
                    $project->name_en = $project_info['name_en'];     
                    $project->hold = $project_info['hold'];     
                    $project->description = $project_info['description'];     
                    $project->sms_sender = $project_info['sms_sender'];     
                    $project->organization_id = $order->organization_id;               
                    $project->age = $project_info['age'];               
                    $project->replica = $project_info['replica'];             
                    $project->project_category_kc_id = $project_info['kc_category'];
                    $project->gender = $project_info['sex'];                    
                    $project->is_private = $project_info['is_private'];
                    $project->is_resale = $project_info['is_resale'];                    
                    $project->save();                    
                    $project_info['id'] = $project->id;
                    $this->flags['project'] = true;
                   // $this->projectRepository->reindexModel($project, true);                    
                }

            // Конец                

            if(!empty($project_info['project_page']) && $order->type == 'API' && isset($project_info['id'])) $this->save_project_page($order, $project_info['project_page'], $project_info['id']);
            if(!empty($project_info['project_gasket']) && $order->type == 'API' && isset($project_info['id'])) $this->save_project_gasket($order, $project_info['project_gasket'], $project_info['id']);

            if(isset($project_info['id']))
                $project_temp['project_result'] = $order->projects()->sync($project_info['id']);
            if(empty($project_info['project_page']) || isset(Auth::user()->id)){
                $order->project_pages()->detach();                                              
                $project_temp['order_id'] = $order->id;                            
                $this->projectHistory($project_temp, $order);        
            }            
        }

        if(!isset($project_info['project_page'])){
            $this->save_project_page($order, $project_info);            
        }              

        
    }    

    public function save_project_page($order, $project_info, $project_id = null){        
        $project_temp = [];
        $project_page = null;
        // При загрузе через API. Если нет Сайта в базе, то создаем его. Начало
        if(isset($project_info['id']))$project_page =  ProjectPage::find($project_info['id']); 
        if($project_page==null){
            $project_page =  ProjectPage::where('import_id', $project_info['import_id'])->first();            
            if($project_page != null){
                $project_info['id'] = $project_page->id;
                $project_info['project_id'] = $project_page->project_id;
            }
        }        

        if($project_page==null  && isset($project_info['import_id']) && !empty($project_info['import_id']) && $project_id != null){
            $project_page = new ProjectPage();
            $project_page->project_id = $project_id;
            if(isset($project_info['import_id']))$project_page->import_id = $project_info['import_id'];
            if(isset($project_info['name']))$project_page->name = $project_info['name'];
            if(isset($project_info['link']))$project_page->link = $project_info['link'];
            if(isset($project_info['private']))$project_page->private = $project_info['private'];
            $project_page->organization_id = $order->organization_id;
            $project_page->save();
            $project_info['id'] = $project_page->id;   
            $project_info['project_id'] = $project_id;   
            $this->flags['project_page'] = true;
            //$this->ProjectPageRepository->reindexModel($project_page, true);  
        }
        // Конец      
        if(isset($project_info['project_id'])){
            $project_temp['project_result'] = $order->projects()->sync($project_info['project_id']);
            $project_temp['page_result'] = $order->project_pages()->sync($project_info['id']);
            $project_temp['order_id'] = $order->id;     
            $this->projectHistory($project_temp, $order);    
        }          
        
    }   

    public function save_project_gasket($order, $project_info, $project_id = null){        
        $project_temp = []; 
        $project_gasket = null;       
        // При загрузе через API. Если нет Сайта в базе, то создаем его. Начало        
        if(isset($project_info['id']))$project_gasket = ProjectGasket::find($project_info['id']); 
        if($project_gasket==null){
            $project_gasket =  ProjectGasket::where('import_id', $project_info['import_id'])->first(); 
            if($project_gasket != null)
            $project_info['id'] = $project_gasket->id;
        }

        if($project_gasket==null  && isset($project_info['import_id']) && !empty($project_info['import_id']) && $project_id != null){
            $project_gasket = new ProjectGasket();
            $project_gasket->project_id = $project_id;
            $project_gasket->import_id = $project_info['import_id'];
            $project_gasket->name = $project_info['name'];
            $project_gasket->link = $project_info['link'];
            $project_gasket->private = $project_info['private'];
            $project_gasket->organization_id = $order->organization_id;
            $project_gasket->save();
            $project_info['id'] = $project_gasket->id;   
            $project_info['project_id'] = $project_id;               
            //$this->ProjectPageRepository->reindexModel($project_page, true);  
        }
        // Конец
        $order->project_gasket()->associate($project_gasket)->save(); 
        //$project_temp['project_result'] = $order->projects()->sync($project_info['project_id']);
        //$project_temp['page_result'] = $order->project_pages()->sync($project_info['id']);
        //$project_temp['order_id'] = $order->id;                            
        //$this->projectHistory($project_temp, $order);
    }   

    public function check_goal($orderData, $project_id){
        $goal_arr = $orderData['goal'];
        $goal = ProjectGoal::firstOrNew([
            'import_id' => $goal_arr['import_id']                    
        ]); 
        $goal->min_price = $goal_arr['min_price_cart'];
        $goal->max_price = $goal_arr['max_price_cart'];
        $goal->import_id = $goal_arr['import_id'];
        $goal->name = $goal_arr['name'];
        $goal->geo_id = $goal_arr['geo_id'];
        $goal->project_id = $project_id;
        $goal->call_center_id = 1;
        $goal->price = $goal_arr['price'];
        $goal->price_currency_id = $goal_arr['price_currency_id'];
        $goal->action_payment = $goal_arr['action_payment'];
        $goal->action_payment_currency_id = $goal_arr['action_payment_currency_id'];
        $goal->web_master_payment = $goal_arr['web_master_payment'];
        $goal->web_master_payment_currency_id = $goal_arr['web_master_payment_currency_id'];
        $goal->additional_payment = $goal_arr['additional_payment'];
        $goal->additional_payment_currency_id = $goal_arr['additional_payment_currency_id'];
        $goal->is_private = ($goal_arr['is_private']=='1')?true:false;
        $exists = $goal->exists;         
        if(isset($goal_arr['geo_id']) && !empty($goal_arr['geo_id']) && (!$exists || ($exists && $goal->geo_id != $goal_arr['geo_id']))){
            $project = Project::find($project_id);
            if($exists)$project->geo()->detach([$goal->geo_id]);
            $project->geo()->syncWithoutDetaching([$goal_arr['geo_id']]);
            $this->flags['project'] = true;            
        }
        if($goal->save()){
            if($goal_arr['geo_id'] != 8 && $goal_arr['geo_id'] != 222 && $goal_arr['geo_id'] != 107 && str_replace("reklpro_id_", "", $goal_arr['import_id']) < 8834){              
                if(isset($orderData['project_info']) && isset($orderData['project_info']['import_id'])){               
                    $rekl_offer_id = str_replace("reklpro_id_", "", $orderData['project_info']['import_id']);
                    $json = json_decode(file_get_contents("http://crmka.pro/api/v1/checker?act=get_project_by_rekl&country_code=".($orderData['country_code'])."&project_id=".$rekl_offer_id."&key=dfkdmflkmsldmkwmw2easq2"), true);                                         
                    if(!empty($json)){
                        if(isset($json['response']) && $json['response']==true){                                                    
                            if(isset($json['fu']['scripts'])){
                                foreach ($json['fu']['scripts'] as $link) {                                    
                                    $script = base_path('public/uploads/scripts/old/'.$json['fu']['id'].'/script/'.$link['link']);
                                    if (file_exists($script)){                                        
                                        $project_goal_scripts = ProjectGoalScript::where('link','scripts/old/'.$json['fu']['id'].'/script/'.$link['link'])->get();
                                        if($project_goal_scripts->count() == 0){
                                            ProjectGoalScript::create([                                
                                                'project_goal_id'        => $goal->id,
                                                'name'                   => $link['name'],
                                                'link'                   => 'scripts/old/'.$json['fu']['id'].'/script/'.$link['link'],
                                                'status'                 => ($link['status']=='1')?true:false,
                                                'views'                  => 1
                                            ]);   
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            return ['id' => $goal->id, 'web_master_payment' => $goal->web_master_payment];
        }  
        
        return [];      
    }

    public function add_sales($order, $sales){
        $sales_ids = [];
        $user_id = null;
        if(isset(Auth::user()->id)){
            $user_id = Auth::user()->id;
        }else{
            $user_id = 1;
        }

        foreach ($sales as $sale) {
            unset($sale['show']);
            unset($sale['cart']);

            if(isset($sale['price'])){
                $quantity_price = $sale['quantity']*$sale['price'];
            }else{

                // Если загружен через API
                if($sale['quantity_price'] == [])
                    $sale['quantity_price'] = 0;
                if($sale['cost_price'] == [])
                    $sale['cost_price'] = 0;

                // $quantity_price = $sale['quantity']*$sale['quantity_price'];
                $quantity_price = $sale['quantity']*$sale['cost_price'];
                
                if(isset($sale['upsale_1']))
                    $sale['upsale'] = 1;
                if(isset($sale['upsale_2']))
                    $sale['upsale'] = 2;
                // $sale['price'] = $sale['quantity_price'];
                $sale['price'] = $sale['cost_price'];

                if(isset($sale['id'])){
                    $sale_temp = Sale::find($sale['id']);
                    // Если нет в текущей базе, то удаляем id чтобы создался новый.
                    if($sale_temp==null){
                        unset($sale['id']);
                    } else {
                        // Если у продажи есть ненулевая стоимость, а с 1с пришёл ноль...
                        if (isset($sale_temp->quantity_price) && $sale_temp->quantity_price > 0 && (int)$sale['cost_price'] == 0) {
                            // то запрещаем обнулять стоимость
                            $quantity_price = $sale_temp->quantity_price;
                            $sale['cost_price'] = $sale_temp->price;
                            $sale['price'] = $sale_temp->price;
                        }
                    }
                }
            }

            if($sale['comment'] == [])
                $sale['comment'] = "";
            if(!isset($sale['weight']) || $sale['weight'] == [])
                $sale['weight'] = "";
            
            $sale['price'] = (int)$sale['price'];
            $sale['quantity_price'] = (int)$quantity_price;
            $sale['quantity'] = (int)$sale['quantity'];

            if(!isset($sale['article']))
                $sale['article'] = "";

            if(isset($sale['id']) && !is_array($sale['id'])){
                $sale_temp = Sale::find($sale['id']);
                if(isset($sale['upsale']) && $sale_temp->upsale != $sale['upsale'])$sale_temp->upsale_user_id = $user_id;
                if($sale_temp->quantity_price != $quantity_price)$sale['quantity_price'] = $quantity_price;
                $sale_temp->update($sale);
                $sales_ids[] = $sale['id'];
            }else{
                if(!isset($sale['upsale']))
                    $sale['upsale'] = 0;

                $sale_temp = new Sale();
                $sale_temp->product_id = isset($sale['product_id']) ?  $sale['product_id'] : 0;
                $sale_temp->name = $sale['name'];
                $sale_temp->price = (int)$sale['price'];
                $sale_temp->quantity = (int)$sale['quantity'];
                $sale_temp->comment = $sale['comment'];
                $sale_temp->upsale = $sale['upsale'];
                $sale_temp->article = $sale['article'];
                $sale_temp->user_id = $user_id;
                $sale_temp->quantity_price = $quantity_price;
                if($sale['upsale'])$sale_temp->upsale_user_id = $user_id;
                $sales_ids[] = $order->sales()->save($sale_temp)->id;
            }
            $this->saleHistory($sale_temp, false, $order);
        }

        $delete_sales = $order->sales->whereNotIn('id', $sales_ids);

        $deletes_count = $order->sales()->whereNotIn('id', $sales_ids)->delete();

        if($deletes_count > 0){
            foreach ($delete_sales as $d_sale) {
                $this->saleHistory($d_sale, true, $order);
            }
        }
    }

    public function validate_order($order, $orderData){
        $projects = $order->projects()->get();
        $errors = [];

        if((count($projects)) == 1){                                      //Если проект один, делаем валидацию
            $project = $projects->first();
            $project_goals = $project->project_goals()->get();
            if((count($project_goals)) > 0){

                $order_geo_id = $order->geo()->get()->last()->id;

                $project_goal = null;                
                foreach ($project_goals as $item){                    
                    if($item->geo()->first()->id == $order_geo_id){                //Гео заказа должно совпадать с гео цели
                        $project_goal = $item;
                        break;
                    }
                }

                $total_price = 0;
                $total_price += $orderData['delivery_types_price'];
                foreach ($orderData['sales'] as $sale) {
                    $total_price += $sale['price']*$sale['quantity'];
                }

                if($project_goal != null && $project_goal->min_price > 0){
                    $min_price = $project_goal->min_price;

                        /*foreach ($order->sales as $sale) {
                            $total_price += $sale->quantity_price;
                        }*/

                        if($total_price <= $min_price){
                            $errors[] = "Стоимость заказа ниже допустимой, минимальная стоимость - $min_price";
                        }

                    }
                   // dd($total_price );
                if($project_goal != null &&  $project_goal->max_price > 0){
                    $max_price = $project_goal->max_price;

                    if($total_price >= $max_price){
                        $errors[] = "Стоимость заказа выше допустимой, максимальная стоимость - $max_price";
                    }
                }

            }
        }

        return $errors;
    }

    public function add_statuses($order, $orderData, $type = ''){

        $statuses = $orderData['statuses'];

        $order_info = $order->statuses()->pluck('status_id','status_type')->toArray();
        $status_change = [];

        $type_1_user_id = $order->statuses()->where('status_type',1)->first()['pivot']['user_id'];


        if(isset( Auth::user()->organization_id)){
            $organization_id = Auth::user()->organization_id;
        }else{
            $organization_id = $order['organization_id'];
        }

        $organization_info = $this->organizationsRepository->searchById($organization_id)->toArray();              

        $lnk = $this->lnkRoleStatusRepository->findWhere([
            'role_id' => $organization_info['role_id']            
        ])->toArray();     

        foreach ($lnk as $lnk_status) {
            $check_statuses[$lnk_status['status_id']] = $lnk_status;
        }   

        $is_invalid = false;     

        $invalid_statuses = $this->checkInvalid($orderData, $type);           

        if(isset($order['phones']) && isset($order['phones'][0]))
        if($this->searchInBlackList($order['phones'][0]) && $type=='new'){
            $is_invalid = true;
            $order->statuses()->attach(52,[
                'user_id'=>1,
                'status_type'=>1,
                'created_at'=>Carbon::now()->format('Y-m-d H:i:s')
            ]);                 
            $this->statusHistory([1=>52], ["id"=>$order["id"]], 1);                                                    
            $order['manager_id'] = 1;    
            $order->save();                
        }

        if(!empty($invalid_statuses) && !$is_invalid){
            $is_invalid = true;
            foreach ($invalid_statuses as $invalid_type => $invalid_id) {
                $order->statuses()->attach($invalid_id,[
                    'user_id'=>1,
                    'status_type'=>$invalid_type,
                    'created_at'=>Carbon::now()->format('Y-m-d H:i:s')
                ]);                 
                $this->statusHistory([$invalid_type=>$invalid_id], ["id"=>$order["id"]], 1);                                                                                
            }
            $order['manager_id'] = 1;    
            $order->save();    
        }

        foreach ($statuses as $status_type=>$status_id) {

            if(!isset($check_statuses[$status_id]))continue;

            if(!$check_statuses[$status_id]['is_view'] || !$check_statuses[$status_id]['is_can_set'])continue;

            $flag = false;

            if(isset($order_info[$status_type])){
                if($status_id==0){
                    $default_status = $this->statusesRepository->findWhere(['parent_id'=> '0','type'=>$status_type])->first();
                    $status_id = $default_status->id;
                }
                if($order_info[$status_type]!=$status_id){
                    $order->statuses()->detach($order_info[$status_type],['status_type'=>$status_type]);
                    $flag = true;
                }
            }else{
                    $flag = true;
            }

            $user_id=0;            

            if(isset(Auth::user()->id)){
                $user_id=Auth::user()->id;
            }else{
                $user_id=1;
            }

            if($is_invalid && $status_type==1)$flag = false;

            if($status_id>0 && $flag){   
                $order->statuses()->attach($status_id,[
                    'user_id'=>$user_id,
                    'status_type'=>$status_type,
                    'created_at'=>Carbon::now()->format('Y-m-d H:i:s')
                ]);
                $status_change[$status_type] = $status_id;                    

                //Если заказ подтвержден и отсутствует статус 5 группы, 
                //устанавливать статус На проверке
                if($status_id == 17 && !isset($order_info[5]) && !isset($statuses[5])){
                    $order->statuses()->attach(103,[
                        'user_id'=>1,
                        'status_type'=>5,
                        'created_at'=>Carbon::now()->format('Y-m-d H:i:s')
                    ]);                     
                    $this->statusHistory([5=>103], ["id"=>$order["id"]], 1);                                                    
                }

                //Если заказ подтвердили, установить время перезвона
                if($status_id == 17){
                    $order['dial_time'] = Carbon::now()->format('Y-m-d H:i:s');    
                    $order->save();
                }
                
                //Если статус первой группы, то меняется менеджер заказа
                if($status_type == 1 && isset(Auth::user()->id)){
                    if(isset($order_info[1]) ){
                        if($status_id != $order_info[1]){
                            $order['manager_id'] = Auth::user()->id;    
                        }
                    }else{
                        $order['manager_id'] = Auth::user()->id;    
                    }
                    $order->save();
                }            
            }            
        }

        $order->save();
        
        $this->statusHistory($status_change, $order);
    }

    public function statusHistory($status_change, $order, $real_manager_id = null){

        if(!empty($real_manager_id)){
            $user_id = $real_manager_id;
        }elseif(isset(Auth::user()->id)){
            $user_id = Auth::user()->id;
        }else{
            $user_id = 1;
        }

        if(!empty($status_change))
            History::create([
                'reference_table' => $this->ordersRepository->model(),
                'reference_id'    => $order["id"],
                'actor_id'        => $user_id,
                'body'            => json_encode(['statuses' => $status_change],JSON_UNESCAPED_UNICODE)
            ]);
    }

    public function saleHistory($sale, $delete = false, $order){
        $sale_db = [];
        $sale_db = $sale->toArray();
        if($sale->wasRecentlyCreated == 1){
            $sale_db['type'] = 'new';
        }elseif(!empty($sale->getChanges())){
            $sale_db['type'] = 'change';
        }elseif($delete){
            $sale_db['type'] = 'delete';

        }
        unset($sale_db['updated_at']);
        unset($sale_db['created_at']);

        $user_id=0;       

        if(isset(Auth::user()->id)){
            $user_id = Auth::user()->id;
        }else{
            $user_id = 1;
        }

        if(isset($sale_db['type']))
                History::create([
                    'reference_table' => $this->ordersRepository->model(),
                    'reference_id'    => $sale->order_id,
                    'actor_id'        => $user_id,
                    'body'            => json_encode(['sales' => $sale_db],JSON_UNESCAPED_UNICODE)
                ]);
    }

    public function projectHistory($project_info, $order){

        if(isset($project_info['project_result']['attached'][0])){
            foreach ($project_info['project_result']['attached'] as $key => $attach) {
                $project_db = [];
                $project_db['project'] = $project_info['project_result']['attached'][$key];
                if(isset($project_info['page_result']['attached'][$key])){
                    $project_db['page'] = $project_info['page_result']['attached'][$key];
                } else {
                    $project_db['page'] = '';
                };

                $user_id=0;
                if(isset(Auth::user()->id)){
                    $user_id = Auth::user()->id;
                }else{
                    $user_id = 1;
                }

                History::create([
                    'reference_table' => $this->ordersRepository->model(),
                    'reference_id'    => $project_info['order_id'],
                    'actor_id'        => $user_id,
                    'body'            => json_encode(['projects' => $project_db],JSON_UNESCAPED_UNICODE)
                ]);
            }
            /*$project_db = [];
            $project_db['project'] = $project_info['project_result']['attached'][0];
            if(isset($project_info['page_result']['attached'][0])){
                $project_db['page'] = $project_info['page_result']['attached'][0];
            } else {
                $project_db['page'] = '';
            };

            $user_id=0;
            if(isset(Auth::user()->id)){
                $user_id = Auth::user()->id;
            }else{
                $user_id = 1;
            }

            History::create([
                'reference_table' => $this->ordersRepository->model(),
                'reference_id'    => $project_info['order_id'],
                'actor_id'        => $user_id,
                'body'            => json_encode(['projects' => $project_db],JSON_UNESCAPED_UNICODE)
            ]);*/
        }
    }

    /*public function saleHistory($sale){
        $sale_db = [];
        //print_r($sale);
        if(isset($sale->names)){
            $sale_db['type'] = 'delete';
            $sale_db['names'] = $sale->names;
        }elseif($sale->wasRecentlyCreated == 1){
            $sale_db['type'] = 'new';
            $sale_db['sale_id'] = $sale->id;
            $sale_db['product_id'] = $sale->product_id;
            $sale_db['name'] = $sale->name;
            $sale_db['price'] = $sale->price;
            $sale_db['upsale'] = $sale->upsale;
            $sale_db['quantity'] = $sale->quantity;
            $sale_db['quantity_price'] = $sale->quantity_price;
        }elseif(!empty($sale->getChanges())){
            $sale_db['type'] = 'change';
            $sale_db['sale_id'] = $sale->id;
            $sale_db = $sale->getChanges();
            if(!isset($sale_db['name']))$sale_db['name'] = $sale->name;
            unset($sale_db['updated_at']);
        }
        if(!empty($sale_db))
                History::create([
                    'reference_table' => 'orders',
                    'reference_id'    => $sale->order_id,
                    'actor_id'        => Auth::user()->id,
                    'body'            => json_encode(['sales' => $sale_db],JSON_UNESCAPED_UNICODE)
                ]);
    }*/

    public function checkInvalid($order, $type){   
        if($type == 'new' && isset($order['import_id']) && stripos($order['import_id'], 'reklpro_') !== false){            

            if(isset($order['client_name']) && strlen($order['client_name'])>1 && ($order['client_name']=='test'||
                $order['client_name']=='Test'||
                $order['client_name']=='Тест'||
                $order['client_name']=='тест'||
                strstr($order['client_name'],'test ')||
                strstr($order['client_name'],'Test ')||
                strstr($order['client_name'],'Тест ')||
                strstr($order['client_name'],'тест ')||
                strstr($order['client_name'],'test-')||
                strstr($order['client_name'],'Test-')||
                strstr($order['client_name'],'Тест-')||
                strstr($order['client_name'],'тест-')||
                strstr($order['client_name'],'test_')||
                strstr($order['client_name'],'Test_')||
                strstr($order['client_name'],'Тест_')||
                strstr($order['client_name'],'робный_заказ')||
                strstr($order['client_name'],'роверка')||
                $order['phones'][0]=='77777777777'||
                strstr($order['client_name'],'тест_'))){
                return [1=>72];
            }elseif(isset($order['client_name']) && strlen($order['client_name'])>1 && (strstr(mb_strtolower($order['client_name']),'ебал')||
                strstr(mb_strtolower($order['client_name']),'cуки')||
                strstr(mb_strtolower($order['client_name']),'Суки')||
                strstr(mb_strtolower($order['client_name']),'cуки')||
                strstr(mb_strtolower($order['client_name']),'Cуки')||
                strstr(mb_strtolower($order['client_name']),'жопу')||
                strstr(mb_strtolower($order['client_name']),'cосите')||
                strstr(mb_strtolower($order['client_name']),'Хуй')||
                strstr(mb_strtolower($order['client_name']),'хуй')||
                strstr(mb_strtolower($order['client_name']),'пошли')||
                strstr(mb_strtolower($order['client_name']),'ахуй')||
                strstr(mb_strtolower($order['client_name']),'4len')||
                strstr(mb_strtolower($order['client_name']),'деньги')||
                strstr(mb_strtolower($order['client_name']),'аебщики')||
                strstr(mb_strtolower($order['client_name']),'идоры')||
                strstr(mb_strtolower($order['client_name']),'уесосы')||
                strstr(mb_strtolower($order['client_name']),'ахуй')||
                strstr(mb_strtolower($order['client_name']),'идарасы')||
                strstr(mb_strtolower($order['client_name']),'верните'))){
                return [1=>198];
            }elseif(isset($order['profit']) && isset($order['real_profit']) && $order['profit']==0 && $order['real_profit']==0){
                return [1=>43];
            }elseif((   
                    !preg_match('/^38/', $order['phones'][0])
                    &&!preg_match('/^7/', $order['phones'][0])
                    &&!preg_match('/^8/', $order['phones'][0])
                    &&!preg_match('/^99/', $order['phones'][0])
                    &&!preg_match('/^37/', $order['phones'][0])
                    &&!preg_match('/^49/', $order['phones'][0])
                    &&!preg_match('/^234/', $order['phones'][0])
                    &&!preg_match('/^371/', $order['phones'][0])
                    &&!preg_match('/^34/', $order['phones'][0])
                    &&!preg_match('/^39/', $order['phones'][0])
                )||
                ( 
                    preg_match('/^779/', $order['phones'][0])
                    ||preg_match('/^70/', $order['phones'][0])
                    ||(
                        preg_match('/^7/', $order['phones'][0])
                        &&(strlen($order['phones'][0])>11)
                      )
                    ||(
                        preg_match('/^374/', $order['phones'][0])
                        &&(strlen($order['phones'][0])!=11)
                      )
                    ||(strlen($order['phones'][0])<10)
                    ||(strlen($order['phones'][0])>12)
                )||(
                    preg_match('/^792981/', $order['phones'][0])
                )){
                return [1=>54];
            }         
        }
        return [];
    }

    public function normalizePhone($phone = ''){
        if(substr($phone, 0, 1)=="8"){
            return '7'.substr($phone, 1, strlen($phone));
        }
        return $phone;
    }       

    public function prepareOrder($order, $permission_list=null)
    {

        $order_calls = $this->callsRepository->search(
            1,
            20,
            null,
            null,
            ['order_id'=>['terms'=>$order->id]]
        ); 

        //$this->CallsService->searchByOrderId($order->id);

        $order->setRelation('calls_t',$order_calls);
        if(isset($order->phones))
            $black_list_info = $this->searchInBlackList($order->phones);

        if(isset($black_list_info)){

            $order->black_list = true;

            $order->black_list_user = $black_list_info['user']['first_name'].' '.$black_list_info['user']['last_name'];

        }

        /*$call_manager = $this->getCallManager($order);

        if($call_manager){

            $order->call_manager =['id'=>$call_manager['id'], 'name' => $call_manager['first_name'].' '.$call_manager['last_name']];

        }*/

        if(isset($order->phones) && $permission_list != null && !$permission_list["menu.main.orders.view_phone_number"] )
            $order = $this->checkPhonePermission($order);

        $order = $this->checkEntities($order);


        $order = $this->prepareHistory($order, $permission_list);

        return $order;
    }

    public function checkPhonePermission($order){

        $phones = [];
        foreach($order->phones as $phone){
            $new_phone = "";
            for($i = 0; $i < strlen($phone);  $i++){
                if($i>strlen($phone)-6){
                    $new_phone .= '?';
                }else{
                    $new_phone .= $phone[$i];
                }
            }
            $phones[] = $new_phone;
        }
        
        $order->phones = $phones;

        return $order;
    }       


    public function searchInBlackList($phones)
    {

        if(!is_array($phones))
            $phones = [$phones];

        foreach ($phones as $phone) {
            $black_list_info = $this->BlackListRepository->searchByParams(['match' => [
                       'phone' => $phone
                   ]
               ],
                   ['id'=>'asc'])->load('user')->toArray();

            if(!empty($black_list_info)){
                return $black_list_info['0'];
            }else{
                return null;
            }
        }
    }

    public function getCallManager($order)
    {
        if($order->statuses){

            $status = $order->statuses->where('type', 1)->last();

            if(!empty($status)){
                $call_manager = $this->UsersRepository->searchByParams(
                        ['match' => [
                                'id' => $status['pivot']['user_id']
                            ]
                        ],
                        ['id'=>'asc']
                    )->toArray();
                if(!empty($call_manager)){
                    return $call_manager['0'];
                }else{
                    return null;
                }
            }
        }

        return null;

    }

    public function checkEntities($order)
    {
        $entities = [
            'menu.main.order.data.history',
            'menu.main.order.data.calls_t'
        ];

        foreach ($entities as $entity) {
            $check = $this->UsersService->can($entity, $order->organization_id);
            if(!$check){
                $keywords = explode(".", $entity);
                $relation = array_pop($keywords);
                $order->{$relation} = null;

            };
        }

        return $order;

    }

    public function prepareHistory($order, $permission_list = null){

        if(!$order->history())return $order;       
        

        $order->history_с = $order->history()->map(function($item) use ($permission_list) {

            $history_body = json_decode($item['body']);

            if(isset($history_body->projects)){

                $project_info = $this->projectRepository->searchByParams(['match' => [
                        'id' => $history_body->projects->project
                    ]
                ],
                    ['id'=>'asc'])->toArray();

                if(!empty($project_info))
                $history_body->projects->project = $project_info[0]['title'];

                if(!empty($history_body->projects->page)) {
                    $page_info = $this->ProjectPageRepository->searchByParams(['match' => [
                        'id' => $history_body->projects->page
                    ]
                ],
                    ['id'=>'asc'])->toArray();
                    if(!empty($page_info))
                    $history_body->projects->page = $page_info[0]['name'] . '('. $page_info[0]['link'] .')';
                }
            };

            if(isset($history_body->main->delivery_types_id)){
                $deliver_type_info = $this->DeliveryTypesRepository->searchByParams(['match' => [
                        'id' => $history_body->main->delivery_types_id
                    ]
                ],
                    ['id'=>'asc'])->toArray();

                if(!empty($deliver_type_info))
                $history_body->main->delivery_types_id = $deliver_type_info[0]['name'];
            }

            if(isset($history_body->statuses)){
                foreach ($history_body->statuses as $key => $value) {
                    $status_info = $this->statusesRepository->searchByParams(['match' => [
                        'id' => $value
                        ]
                    ],
                        ['id'=>'asc'])->toArray();
                    if(!empty($status_info))
                        $history_body->statuses->$key=$status_info[0]['name'];
                }
            }

            if(isset($history_body->main->phones)){      
                if($permission_list != null && !$permission_list["menu.main.orders.view_phone_number"]){
                    $phones = json_decode($history_body->main->phones);
                    $temp_phones = [];
                    foreach ($phones as $phone) {
                        $new_phone='';
                        for($i = 0; $i < strlen($phone);  $i++){
                            if($i>strlen($phone)-6){
                                $new_phone .= '?';
                            }else{
                                $new_phone .= $phone[$i];
                            }
                        }
                        $temp_phones[] = $new_phone;                       
                    }    
                    $history_body->main->phones = json_encode($temp_phones);                    
                }                    
            }

            if(isset($history_body->sales->upsale)){
                if($history_body->sales->upsale==false)$history_body->sales->upsale = 'Снято';
            }

            //$this->DeliveryTypesRepository

            if(isset($history_body->sales)){
                unset($history_body->sales->product_id);
                unset($history_body->sales->user_id);
                unset($history_body->sales->order_id);
                unset($history_body->sales->id);
                unset($history_body->sales->is_cart);
                unset($history_body->sales->upsale_user_id);
            }
            
            unset($history_body->main->info);

            $item['body'] = $history_body;
            return $item;
        });

        $temp = [];
        $temp_2 = [];

        foreach ($order->history_с as $h_item) {
            $date_f = $h_item->created_at->format('Y-m-d H:i:s');
            $name_f = $h_item->users->first_name.' '.$h_item->users->last_name;
            $arr = $h_item->toArray();

            $temp[$date_f][$name_f][] = $arr['body'];                    
        }

        foreach ($temp as $created_at => $body_cont) {
            foreach ($body_cont as $actor_id => $body) {
                $temp_2[] = ['created_at'=>$created_at, 'user_name'=>$actor_id, 'body'=>$body];    
            }            
        }

        $order->history_с = collect($temp_2);
        
        return $order;
    }

    public function changeManagerId($key, array $orderData)
    {        
        if($this->UsersService->can("menu.main.orders.edit_order_manager", Auth::user()->organization_id)){
            if(isset($orderData['manager_id'])){
                $order = $this->ordersRepository->updateBy(['manager_id'=>$orderData['manager_id']], $key, "key");
                if ($order) {
                    $this->ordersRepository->reindexModel($order, true);
                    return true;
                }
            }
        }
        return false;
    }

    protected function prepareModel($data, $repo)
    {
        if(isset($data['import_id'])) {
            $model  = $this->ordersRepository->findAllBy('import_id', $data['import_id'])->first();
        }
        else {
            $model  = $this->ordersRepository->find($data['id']);

            $data['import_id']  = $data['id'];
        }

        unset($data['id']);
        if($model) {
            throw new RuntimeException();
        }


        $model  = $repo->create($data);

        return $model;
    }

    public function get_key($id = 0)
    {
        return substr(md5(base64_encode($id)), 0, 10);
    }

    protected function addSearchConditions(User $user=null,array $filters=null)
    {
        return $filters;
    }

    public function pgSearch($request)
    {
        return $this->ordersRepository->pgSearch($request);
    }

    public function dxGroupedSearch($request)
    {
        $result = $this->ordersRepository->dxGroupedSearch($request);
        return $result;
    }

    protected function getSearchRepository()
    {
        return $this->ordersRepository;
    }

    public function getPermissionQuery(){
        return $this->permissionQuery;
    }

    public function getExportToExcelLib(){
        return $this->exportToExcel;
    }

    public function defineSex($client_name)
    {
        $sex_id = 0;
        $sex = null;

        $filename = "sex_library.json";
        $path = storage_path()."/app/files/json/${filename}";
        $json = json_decode(file_get_contents($path), true);
        $names = explode(" ", $client_name);
        $names = array_map('mb_strtolower', $names);

        if ($json){
            foreach ($names as $name){
                foreach ($json as $item) {
                    if ($item && $item['name'] == $name) {
                        $sex = $item['sex'];
                        break;
                    }
                }
            }
        }

        if(isset($sex)){
            if ($sex == 'mr'){
                $sex_id = 1;
            }
            elseif ($sex == 'ms'){
                $sex_id = 2;
            }
        }

        return $sex_id;
    }
    
    private function getCurrentCallPhone($operator_id)
    {
        
        $ats_users_online = DB::table('ats_users as au')
            ->where('au.user_id', $operator_id)
            ->join('user_status_logs as l', 'l.ats_user_id', '=', 'au.id')
            ->join('ats_statuses as as', function($jin) {
                $jin->on('as.id', '=', 'l.status_id')
                    ->whereRaw("lower(name_en) = 'online'");
            })
            ->select(
                'au.id',
                'au.login',
                'au.user_id',
                'as.name_ru',
                'as.name_en',
                'l.created_at'
                );
            
        $ats_user = DB::table('ats_users as au')
            ->where([['au.user_id', $operator_id], ['au.type', 'privat']])
            ->join('user_status_logs as l', 'l.ats_user_id', '=', 'au.id')
            ->join('ats_statuses as as', function($jin) {
                $jin->on('as.id', '=', 'l.status_id')
                    ->whereRaw("lower(name_en) = 'speak'");
            })
            ->select(
                'au.id',
                'au.login',
                'au.user_id',
                'as.name_ru',
                'as.name_en',
                'l.created_at'
                )
            ->union($ats_users_online)
            ->orderBy('created_at', 'desc')
            ->first();
            
        if (!$ats_user) {
            $this->pushError(["Not found", 404, ["AtsUser not found"]]);
            return false;
        }
        
        $ats_user = AtsUser::where('id', $ats_user->id)->with('atsGroup')->first();
            
        $caller = DB::table('sip_caller_ids as cid')
            ->where('cid.ats_user_id', $ats_user->id)
            ->select(
                'cid.caller_id'
                )
            ->first();
        
        $ats = Ats::find($ats_user->atsGroup->ats_id);
        $link = "http://$ats->ip/aster_api/APIAsterisk.php?key=$ats->key&act=get_current_extension_by_agent&agent=$caller->caller_id";
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $link,
            CURLOPT_RETURNTRANSFER => true,
        ));
        $response = curl_exec($curl); 
        // $response = '{"result":true,"params":{"Extension":"374000000036"},"msg":"action complite"}'; // TEMP: Имитируем ответ от сервера АТС. Закомментить перед коммитом!
        curl_close($curl);
        
        $result = json_decode($response);
        if (!$result) {
            $this->pushError(["Response error", 500, [$response]]);
            return false;
        }
        if (!$result->result) {
            $this->pushError(["Error", 422, [$result]]);
            return false;
        }
        if (empty($result->params->Extension)) {
            $this->pushError(["Error", 422, ['msg'=>"No extension in response", 'response'=>$result]]);
            return false;
        }
        
        return [
            'phone' => $result->params->Extension,
            'caller_id' => $caller->caller_id,
        ];
    }

    public function getByOperator($operator_id)
    {
      /*- Поиск карточки заказа по номеру телефона клиента.
        Условия:
        =========================

        Шаг 1 Делаем поиск звонка в таблице звонков по условию:
        1.1) поле phone совпадает с номером телефона клиента.
        1.2) Берем последний созданный звонок на этот номер телефона.
        1.3) Поле order_id у звонка не пустое и не равно нулю.
        Если три условия совпадут, выдавать пользователю ту карточку которая указана в order_id звонка.
        1.4) Пишем статус к звонку с параметрами

        статус = connect
        айди звонка
        Текущее время.
        в статусе должен зафиксироваться каллер айди оператора который вызвал метод.
        Если звонок не найден пропускаем шаг.
        =========================

        Шаг 2 Если не выполнился шаг 1, делаем поиск по шагу 2.
        Делаем поиск заказа в таблице заказов по условию:
        2.1) Ищем последний созданный заказ по основному номеру телефона который указан в заказе.
        2.2) Берем из заказа айди.

        Если заказ не удалось найти метод должен возвращать ошибку, если заказ найден выдавать айди найденного заказа.                                                                                                       */
        
        function err($arr, $tab = '') {
            if ($tab == '') info("getByOperator() error");
            foreach ($arr as $key => $value) {
                if (is_array($value)) {
                    info("$key =>");
                    err($value, $tab . '    ');
                    continue;
                }
                info("$tab$key => $value");
            }
        }
        
        function hide_phone($phone) {
            $phone_length = strlen($phone);
            return substr($phone, 0, $phone_length-3).'***';
        }
        
        $this->clearErrors();
        
        $data = $this->getCurrentCallPhone($operator_id);
        if ($this->errors()) return false;
        $phone = $data['phone'];
        
        /***********************
         *        Шаг 1        *
         ***********************/

        $call = DB::table('calls')->where([['phone', $phone], ['order_id', '>', 0]])->orderBy('id', 'desc')->first();
        
        if ($call) {
            $call_status = new CallStatus;
            $call_status->status = 'connect';
            $call_status->call_id = $call->id;
            $call_status->time = date("Y-m-d H:i:s");
            $call_status->agent = $data['caller_id'];
            $call_status->save();
            $order = Order::find($call->order_id);
            if ($order && isset($order->key)) {
                return $order->key;
            }
        }
        
        /***********************
         *        Шаг 2        *
         ***********************/
        
        $order = $this->ordersRepository->searchByParams(['match' => ['phones' => $phone]], ['id'=>'desc']);
        if (count($order) == 0) {
            err(['msg'=>"Order not found", 'phone'=>$phone, 'response'=>$data, 'operator_id'=>$operator_id]);
            if (isset($data['phone'])) $data['phone'] = hide_phone($data['phone']);
            return  ['msg'=>"Order not found", 'phone'=>hide_phone($phone), 'response'=>$data, 'operator_id'=>$operator_id];
        }
        
        if ($order[0] && isset($order[0]->key)) {
            return $order[0]->key;
        }
        
        err(['msg'=>"Order not found", 'phone'=>$phone, 'response'=>$data, 'operator_id'=>$operator_id]);
        if (isset($data['phone'])) $data['phone'] = hide_phone($data['phone']);
        return ['msg'=>"Order not found", 'phone'=>hide_phone($phone), 'response'=>$data, 'operator_id'=>$operator_id];
    }
    
    public function getOrdersByQueue($ats_queue_arr = [], $api_key, UnloadsService $unloadsService, $check_date = true)
    {
        //Получение актуальных часовых поясов
        $work_time_filters = $this->geoService->get_time_zone_by_work_time($ats_queue_arr);      

        //Лимит заказов
        $take = 5000;
 
        $add_filter = [];                
        $add_filter_2 = [];                

        $i = 1;        
        $i_2 = 1;     

        //Добавление фильтра по часовым поясам
        foreach ($work_time_filters as $time_zone) {            
            $add_filter[] = ["time_zone", "=", $time_zone];
            if($i != (count($work_time_filters)))$add_filter[] = "or";
            $i++;
        }             

        foreach ($work_time_filters as $time_zone) {            
            $add_filter_2[] = ["time_zone", "notcontains", $time_zone];
            if($i_2 != (count($work_time_filters)))$add_filter_2[] = "and";
            $i_2++;
        } 

        $now_reg = date('Y/m/d H:i:s',(strtotime('now') - (60*60)));            

        $add_filter_2 = [$add_filter_2];

        $add_filter_2[] = "and";
        $add_filter_2[] = ["created_at",">",$now_reg];            

        //Без сортировки
        $add_sort = [];
        //$add_sort[] = ["selector"=>"dial_step","desc"=>false];

        //Добавление полей шагов перезвона в выдачу
        $add_list = [];
        $add_list[] = "dial_steps";

        //Доп.параметры - ID очереди
        $add_conditions = [];
        $add_conditions = ['queue_id'=>$ats_queue_arr['id']];

        //Получение заказов
        
        //В часовых поясах
        $orders_1 = $unloadsService->getOrdersByAPIKey($api_key, 0, $take, $add_filter, $add_sort, $add_list, $add_conditions);

        //Созданные за последний час
        $orders_2 = $unloadsService->getOrdersByAPIKey($api_key, 0, ($take-count($orders_1)), $add_filter_2, $add_sort, $add_list, $add_conditions);

        $orders = array_merge($orders_1, $orders_2);  

        //текущее время
        $now = strtotime('now')+30;

        if ($check_date) {
            //Проверка на рабочее время
            $orders = array_filter($orders, function($item) use ($now){                        
                if($item['dial_time'] < $now)return $item;
            });        
        }

        //Сортировка по dial_step
        usort($orders, function ($a, $b) {
            return $a['dial_step'] <=> $b['dial_step'];
        });
        
        return $orders;
    }

    /**
     * Массово устанавливает dial_time для заказов
     * @method setDialTimes
     * @param  Array $order_ids Массив массивов, где каждый вложенный массив имеет ключ, 
     *                          являющийся ID очереди, а сам массив содержит ID заказов
     *                          Пример: $order_ids = ["5" => [1412069, 1412401], "4" => [1409127, 1412371]];
     * @param  integer $dial_time Количество обновлённых записей.
     */
    public function setDialTimes($order_ids, $dial_time)
    {
        $result = 0;
        $now = now();
        $reindex = [];
        // $dial_time = $dial_time->addHours(rand(0,50));
        foreach ($order_ids as $queue_id => $orders) {
            $exists = DB::table('orders_dial_steps')
                ->whereIn('order_id', $orders)
                ->where('queue_id', $queue_id)
                ->pluck('order_id')->toArray();
            $result += DB::table('orders_dial_steps')
                ->whereIn('order_id', $exists)
                ->where('queue_id', $queue_id)
                ->update(['dial_time' => $dial_time, 'updated_at' => $now]);
                
            $new = array_diff($orders, $exists);
            $data = [];
            foreach ($new as $order_id) {
                $data[] = [
                    'queue_id' => $queue_id,
                    'order_id' => $order_id,
                    'dial_step' => 0,
                    'dial_time' => $dial_time,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }
            DB::table('orders_dial_steps')->insert($data);
            
            $reindex = array_merge($reindex, $orders);
        }
        
        $collection = [];
        foreach ($reindex as $key => $value) {
            $order = $this->ordersRepository->find($value);
            if ($order) {
                $collection[] = $order;
            }
        }
        $this->ordersRepository->reindexByData(collect($collection));

        return $result;
    }
    
    public function refuseOrder($id){
        $data= [];
        $user_id = Auth::user()->id;
        $call = Call::where('order_id', $id)
            ->where('user_id', '!=', $user_id)
                ->where('disposition', 'answered')
                    ->where('billing_time', '>', 60)->first();

        if($call){
            $order = Order::find($id);
            $order->operator_id = $order->manager_id = $call->user_id;              // закрепляем заказ за опертаором найденым по звонку
            $order->update();
            $this->ordersRepository->reindexModel($order, true);
            $data['message'] = "Отказ закреплен за первым говорившим.";
            $data['status'] = 200;
        }else{
            $data['message'] = "Разговор другого оператора длительностью более 60 сек не найден в карточке заказа";
            $data['status'] = 404;
        }

        return $data;
    }
}
