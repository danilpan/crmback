<?php

namespace App\Console\Commands;

use App\Models\AtsStatus;
use App\Models\Order;
use App\Models\Organization;
use App\Models\User;
use App\Repositories\CallsRepository;
use Illuminate\Console\Command;
use App\Services\OrganizationsService;
use App\Services\StatusesService;
use App\Services\OrdersService;
use App\Services\UsersService;
use Elasticsearch\Client as ElasticClient;
use App\Repositories\OrdersRepository;

use DB;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Telegram\Bot\Laravel\Facades\Telegram;

class GetKcStat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'GetKcStat:set {--function=test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Статистика по колл-центрам';

    private $sum = []; 

    private $status_approve = []; 
    private $status_waits = []; 
    private $status_refusals = []; 
    private $status_musor = []; 
    private $org_names = []; 
    private $now; // Current datetime

    protected $organizationsService;
    protected $ordersService;
    protected $usersService;
    protected $statusesService;
    protected $client;
    protected $ordersRepository;
    protected $callsRepository;


    /**
     * Create a new command instance.
     *
     * @param OrdersRepository $ordersRepository
     * @param OrganizationsService $organizationsService
     * @param StatusesService $statusesService
     * @param UsersService $usersService
     * @param OrdersService $ordersService
     * @param ElasticClient $client
     */
    public function __construct(
        OrdersRepository $ordersRepository,
        OrganizationsService $organizationsService,
        StatusesService $statusesService,
        UsersService $usersService,
        OrdersService $ordersService,
        ElasticClient $client,
        CallsRepository $callsRepository
    )
    {
        parent::__construct();
        $this->ordersRepository = $ordersRepository;
        $this->organizationsService = $organizationsService; 
        $this->statusesService = $statusesService; 
        $this->usersService = $usersService;
        $this->ordersService = $ordersService;
        $this->client = $client; 
        $this->callsRepository = $callsRepository;
        define('MAIN_KC_ID', env('MAIN_KC_ID', 3));
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $type     = $this->option('function');

        $orders = [];

        switch ($type){
            case 'get_stat':
                $orders = $this->get_stat();
                break;
            case 'approves_per_hour':
                $this->sendApprovesPerHourReport();
                break;
            case 'staff_hours':
                $this->getKcStaffHoursReport();
                break;
            case 'add_sales':
                $this->getAddSalesReport();
                break;
            case 'phoned_orders':
                $this->getPhonedOrders();
                break;
            case 'manual_calls':
                $this->getManualCallsReport();
                break;
        }

    }    
    
    private function now()
    {
        // return Carbon::parse('2019-06-03 12:00:00'); // TEMP: Для удобства тестирования здесь можно глобально переопределить текущие дату и время
        return Carbon::now();
    }

    public function get_stat(){    
        $hour = (int)date('H');
        
        if ($hour < 9) {
            exit('Нерабочее время');   
        }

        if ($hour > 18) {
            exit('Нерабочее время');   
        }         

        $users = [];
        $users_final = [];
        $orgs = [];       
        
        $this->get_statuses(); //Получение актуальных статусов    
        $users = $this->get_users(3); //Получение ID пользователей в разрезе КЦ             
        
        foreach ($users as $org_id => $user_arr) {
            $users_final[$org_id] = $this->count_statuses($user_arr);
        }

        $title = date('Y-m-d H:i:s');

        $text = "Отчет по апруву в разрезе КЦ\n";
        $text .= $title."\n\n";                 

        foreach ($users_final as $org_id => $kc_info) {
            if($kc_info['count']>0){
                $text .=  $this->org_names[$org_id]."\n";
                $clean = $kc_info['waits']+$kc_info['confirmed']+$kc_info['refusals'];
                $ring = $kc_info['confirmed']+$kc_info['refusals'];
                
                if($clean!=0){
                    $text .= (round($kc_info['confirmed'] / $clean, 2))." - Чистый\n";
                }else{
                    $text .= "0 - Чистый\n";
                }

                if($ring!=0){
                    $text .= (round($kc_info['confirmed'] / $ring, 2))." - Обработанный\n";
                }else{
                    $text .= "0 - Обработанный\n";
                }

                if($kc_info['count']!=0){
                    $text .= (round(($kc_info['confirmed'] / $kc_info['count']), 2))." - Общий\n";
                }else{
                    $text .= "0 - Общий\n";
                }

                if($kc_info['confirmed']!=0){
                    $text .= (round(($kc_info['sales_additional_count'] / $kc_info['confirmed']), 2))." - % доп.продаж\n\n";            
                }else{
                    $text .= "0 - % доп.продаж\n";
                }
            }           
        }   
        $this->storeMessage($text);
        dd($text);        

        /*foreach ($data as $order) {
            foreach ($users as $org_id => $users_arr) {
                if(in_array($order['manager_id'], $users_arr)){
                    $this->check($order,$org_id);
                }
            }            
        };

        dd($this->sum);

        $orders = [];*/
    }

        

    private function count_statuses($users){        
        $final = [];
        $final['count'] = 0;
        $final['confirmed'] = 0;                    
        $final['sales_additional_count'] = 0;    
        $final['waits'] = 0;    
        $final['refusals'] = 0; 
        $final['musor'] = 0;                     
        $params = [];
        $params = [
            'index'     => $this->ordersRepository->getIndex(),
            'type'      => $this->ordersRepository->getType(),
            'size'      => 0,
            'from'      => null,
            'body' => [
                'sort'  => [],
                'query' => []
            ]
        ];

        $params['body']['query']['constant_score']['filter']['bool']['must'] = [];        

        $filter['must']['bool']['must'][]['range']['status_1.created_at'] = ['gt'=>date('Y-m-d H:i:s')]; //C начала суток      

        $managers = [];                
        foreach ($users as $org_id=>$user) {                        
            $managers[] = [
                'term'=>['manager_id'=>$user]
            ];    
        }
        $filter['must']['bool']['must'][]['bool']['should'] = $managers;
      
        $params['body']['aggs']['status']['terms'] = ['field' => 'statuses.id',"size" => 100000];  //В разрезе статусов
        
        $params['body']['aggs']['status']['aggs']['upsale_1_sum']['sum'] = ['field' => 'upsale_1_sum']; //сумма апсейлов

        $params['body']['query']['constant_score']['filter']['bool']['must'] = $filter['must'];         

        $result = $this->client->search($params);

        if($result['hits']['total']>0){

            $final['count'] = $result['hits']['total'];
            foreach($result['aggregations']['status']['buckets'] as $status_info){                
                if(in_array($status_info['key'], $this->status_approve)){                                                
                    $final['confirmed'] += $status_info['doc_count'];                    
                    $final['sales_additional_count'] += $status_info['upsale_1_sum']['value'];    
                }elseif(in_array($status_info['key'], $this->status_waits)){                    
                    $final['waits'] += $status_info['doc_count'];    
                }elseif(in_array($status_info['key'], $this->status_refusals)){                    
                    $final['refusals'] += $status_info['doc_count']; 
                }elseif(in_array($status_info['key'], $this->status_musor)){                                
                    $final['musor'] += $status_info['doc_count'];                     
                }    
            }
         
        }        
        return $final;       

    }

    //Получение ID пользоветелей
    private function get_users($id){
        $final_orgs = [];
        $users = [];
        $orgs = $this->organizationsService->getChildsById($id);
        foreach ($orgs as $org) {            
            $final_orgs[$org->id] = $this->re_construct([$org]);
            $this->org_names[$org->id] = $org->title; //Сохрание названия орг-ции
        }
        foreach ($final_orgs as $org_id => $orgs_arr) {
            $temp = $this->usersService->searchUsersByOrgs($orgs_arr); //Получение пользователей по массиву ID организации
            foreach ($temp as $user) {
                $users[$org_id][] = $user->id; 
            }               
        }
        return $users;
    }    

    //Получение статусов
    private function get_statuses(){
        //Статусы подтверждения
        $temp_approve = $this->statusesService->getChildsById(17);        ;
        if($temp_approve->count()){
            $this->status_approve = $this->re_construct($temp_approve);            
        }
        $this->status_approve[] = 17;
        //Статусы ожидания
        $temp_waits = $this->statusesService->getChildsById(18);        
        if($temp_waits->count()){
            $this->status_waits = $this->re_construct($temp_waits);            
        }
        $this->status_waits[] = 18;
        //Статусы отклоенения
        $temp_refusals = $this->statusesService->getChildsById(19);        
        if($temp_refusals->count()){
            $this->status_refusals = $this->re_construct($temp_refusals);            
        }
        $this->status_refusals[] = 19;
        //Статусы мусора
        $temp_musor = $this->statusesService->getChildsById(58);        
        if($temp_musor->count()){
            $this->status_musor = $this->re_construct($temp_musor);            
        }
        $this->status_musor[] = 58;

    }

    private function re_construct($collection){
        $ids = [];        
        foreach ($collection as $item) {                  
            if($item->childs->count()){                               
                $ids = array_merge($this->re_construct($item->childs),$ids);
            };
            $ids[] = $item['id'];
        }        
        return $ids;
    }




    private function check($order=array(), $org_id='none')
    {
        dd('не работает'); 
        if(!isset($this->sum[$org_id]['count']))$this->sum[$org_id]['count'] = 0;
        $this->sum[$org_id]['count'] += 1;        
        if(in_array($order['status_1']['id'], $this->status_approve)){        
            if(!isset($this->sum[$org_id]['confirmed']))$this->sum[$org_id]['confirmed'] = 0;
            $this->sum[$org_id]['confirmed'] += 1;
            if(!isset($this->sum[$org_id]['sales_additional_count']))$this->sum[$org_id]['sales_additional_count'] = 0;
            $this->sum[$org_id]['sales_additional_count'] += $order['upsale_1_sum'];    
        }elseif(in_array($order['status_1']['id'], $this->status_waits)){
            if(!isset($this->sum[$org_id]['waits']))$this->sum[$org_id]['waits'] = 0;
            $this->sum[$org_id]['waits'] += 1;    
        }elseif(in_array($order['status_1']['id'], $this->status_refusals)){
            if(!isset($this->sum[$org_id]['refusals']))$this->sum[$org_id]['refusals'] = 0;
            $this->sum[$kc]['refusals'] += 1; 
        }elseif(in_array($order['status_1']['id'], $this->status_musor)){            
            if(!isset($this->sum[$org_id]['musor']))$this->sum[$org_id]['musor'] = 0;
            $this->sum[$kc]['musor'] += 1;                     
        }            
    }

    private function full_manager_id(){
        dd('не работает'); 
        $new_collection = [];
        $statuses = DB::table('order_status')            
            ->select('*')            
            ->where('status_type',1)
            ->where('user_id','>',0)            
            ->get()->toArray();
        foreach ($statuses as $status) {            
            $order = $this->ordersRepository->find($status->order_id); 

            $order->manager_id = $status->user_id;

            $order->save();

            var_dump($order->id);

            $new_collection[] = $order;
        }
        $this->ordersRepository->reindexByData(collect($new_collection));  
    }

    private function sendApprovesPerHourReport()
    {
        $date = date('Y-m-d H:i:s');
        $title = "Отчёт по количествуву апрувов в час.\n".$date."\n\n";
        $message = $title;
        $data = $this->getApprovesPerHour();
        arsort($data);
        foreach ($data as $key=>$datum){
            $message .= $key." - ".$datum." заказов в час\n";
        }

        $this->storeMessage($message);
    }
    
    private function getKcStaffHoursReport()
    {
        $date = date('Y-m-d H:i:s');
        $title = "Отчёт по кол-ву часов операторов в штате.\n".$date."\n\n";
        $message = $title;
        $data = $this->getKcStaffHours();

        foreach ($data as $key=>$datum){
            $org = Organization::find($key)->title;
            $message .= $org." - ".$datum." ч\n";
        }

        $this->storeMessage($message);
    }

    private function getApprovesPerHour()
    {
        $ordersRepository = $this->ordersRepository;
        $kc_childs = Organization::where('parent_id', MAIN_KC_ID)->get();           // Дочерние КЦ 1 уровня
        $kc_childs_ids = [];                                                        // ID всех дочерних группированные по КЦ 1 уровня

        foreach ($kc_childs as $child){
            $kc_childs_ids[$child->id] = $child->getChildIds();
        }

        // TODO: Оптимизировать этот ужас =)
        $organizations_online_time  = [];
        foreach ($kc_childs_ids as $key=>$kc_childs) {
            $org_time = 0;
            foreach ($kc_childs as $kc_child_id) {
                $org = Organization::find($kc_child_id);
                if($org) {
                    $users = $org->users()->get();
                    foreach ($users as $user) {
                        $org_time += $this->getUserOnlineTime($user);
                    }
                }
            }
            if($org_time > 0) {
                $organizations_online_time[$key] = $org_time;                                   // Время на линии по всем КЦ
            }
        }
        
        $filter = [
            ['status_1.id', '=', 17],
            'and',
            ['status_1.created_at', '>=', $this->now()->subHours(24)->toDateTimeString()]
        ];
        $request = $ordersRepository->makeRequest(0, 1000, $filter);

        $orders = $ordersRepository->dxSearchAll($request)->groupBy('operator.organizations.id');

        $organization_confirmed_orders = [];
        foreach ($kc_childs_ids as $kc_key=>$kc_childs_id) {
            foreach ($orders as $key=>$organization_orders){
                if (in_array($key, $kc_childs_id))
                    $organization_confirmed_orders[$kc_key] = count($organization_orders);      // Кол-во подвтержденных по всем КЦ
            }
        }
        $approves_per_hour = [];
        foreach ($organizations_online_time as $kc_key=>$time){
            foreach ($organization_confirmed_orders as $org_key=>$orders) {
                if($kc_key == $org_key && $time>0){
                    $key = Organization::find($kc_key)->title;
                    $approves_per_hour[$key] = round($orders/($time/60), 1);      // Аппрувы в час по всем КЦ
                }
            }
        }

        return $approves_per_hour;
    }
    
    private function getKcStaffHours()
    {
        $kc_childs = Organization::where('parent_id', MAIN_KC_ID)->get();           // Дочерние КЦ 1 уровня
        $kc_childs_ids = [];                                                        // ID всех дочерних группированные по КЦ 1 уровня

        foreach ($kc_childs as $child){
            $kc_childs_ids[$child->id] = $child->getChildIds();
        }

        $organizations_online_time  = [];
        foreach ($kc_childs_ids as $key=>$kc_childs) {
            $org_time = 0;
            foreach ($kc_childs as $kc_child_id) {
                $org = Organization::find($kc_child_id);
                if($org) {
                    $users = $org->users()->get();
                    foreach ($users as $user) {
                        $org_time += $this->getUserOnlineTime($user);
                    }
                }
            }
            if($org_time > 0) {
                $organizations_online_time[$key] = round($org_time/60, 1);                                   // Время на линии по всем КЦ
            }
        }

        return $organizations_online_time;
    }

    private function getUserOnlineTime(User $user)
    {
        $atsUsers = $user->atsUsers()->get();
        $time = 0;
        $online_status_id = AtsStatus::where('name_en', 'Online')->first()->id;             // айди статуса "Онлайн"
        $ringing_status_id = AtsStatus::where('name_en', 'Ringing')->first()->id;           // айди статуса "Не берет трубку"
        $speak_status_id = AtsStatus::where('name_en', 'Speak')->first()->id;               // айди статуса "В разговоре"
        $online_statuses_ids = [$online_status_id, $ringing_status_id, $speak_status_id];   // массив айдишек рабочего времени

        if ($atsUsers){
            foreach ($atsUsers as $atsUser) {
                $statuses = DB::table('user_status_logs')
                    ->where('created_at', '>=', $this->now()->subHours(24)->toDateTimeString())
                    ->where("ats_user_id", $atsUser->id)
                    ->get();

                if(!empty($statuses)) {
                    // BUG: Не анализируется время с начала отчёта по первый статус. Надо получать последний статус перед началом отчёта и анализировать его. Если статус входит в массив $online_statuses_ids, то надо добавить время с начала отчёта по первый статус. Баг встречается везде, где считается время на линии. Описан в комментариях к задаче crm2-84
                    foreach ($statuses as $key => $value) {
                        if (in_array($value->status_id, $online_statuses_ids)) {

                            $from = Carbon::parse($value->created_at);
                            if (isset($statuses[$key + 1])) {
                                $to = Carbon::parse($statuses[$key + 1]->created_at);
                            } else {
                                $to = $this->now()->toDateTimeString();
                            }
                            $diff = ceil($from->diffInSeconds($to) / 60);

                            if (in_array($value->status_id, $online_statuses_ids)) {
                                $time += $diff;
                            }
                        }
                    }
                }
            }
        }

        return $time;
    }

    private function getAddSales()
    {
        $kc_childs = Organization::where('parent_id', MAIN_KC_ID)->get();           // Дочерние КЦ 1 уровня
        $kc_childs_ids = [];                                                        // ID всех дочерних группированные по КЦ 1 уровня

        foreach ($kc_childs as $child){
            $kc_childs_ids[$child->id] = $child->getChildIds();
        }

        $filter = [
            ['status_1.id', '=', 17],
            'and',
            ['status_1.created_at', '>=', $this->now()->subHours(24)->toDateTimeString()]
        ];
        $request = $this->ordersRepository->makeRequest(0, 1000, $filter);

        $orders = $this->ordersRepository->dxSearchAll($request)
            ->groupBy(['geo.code', 'operator.organizations.id', function ($item) {
                if (count($item->project_category_kc) > 0) {
                    return $item->project_category_kc[0]['title'];
                } else {
                    return "Без категории";
                }
            }]);
        $geo_add_sales = [];

        foreach ($orders as $geo_key => $geo_orders){
            $org_add_sales = [];
            foreach ($geo_orders as $org_key => $organization_orders){
                foreach ($kc_childs_ids as $kc_key=>$kc_childs_id){
                    $cat_add_sales = [];
                    foreach ($organization_orders as $cat_key => $cat_orders){
                        if ($cat_key == "")
                            $cat_key = "Без категории";
                        $upsales_count = 0;
                        $sales_count = 0;
                        foreach ($cat_orders as $order) {
                            if (!empty($order['sales'])) {
                                $sales_count += count($order['sales']);
                                foreach ($order['sales'] as $sale) {
                                    if ($sale['upsale'] > 0) {
                                        $upsales_count++;
                                    }
                                }
                            }
                        }
                        if($sales_count > 0){
                            $cat_add_sales[$cat_key] = round(100*$upsales_count/$sales_count, 1);
                        }
                    }
                    if (in_array($org_key, $kc_childs_id)){
                        $org_add_sales[$org_key] = $cat_add_sales;
                    }
                }
            }
            $geo_add_sales[$geo_key] = $org_add_sales;
        }

        return $geo_add_sales;

    }

    private function getAddSalesReport()
    {
        $title = "Ежедневный отчёт по доп.продажам, в разрезе типов офферов.\n";
        $date = date('Y-m-d H:i:s');
        $message = $title.$date."\n\n";

        $data = $this->getAddSales();

        foreach ($data as $geo_key => $geo){
            $message .= $geo_key."\n";
            foreach ($geo as $org_key => $org){
                $org_title = Organization::find($org_key)->title;
                $message .= "\t\t\t\t$org_title\n";
                foreach ($org as $category_key => $category){
                    $message .= "\t\t\t\t\t\t\t\t$category_key - $category\n";
                }
            }
            $message .= "\n";
        }
        dd($message);
        $this->storeMessage($message);

    }

    private function getPhonedOrders()
    {
        $answered = DB::table('calls as c')
            ->whereNotNull('c.order_id')
            ->where([
                ['c.time', '>=', $this->now()->subHours(24)->toDateTimeString()],
                ['c.disposition', '=', 'answered']
            ])
            ->join('orders as o', 'o.id', '=', 'c.order_id')
            ->pluck('o.key')
            ->toArray();
        
        $not_answered = DB::table('calls as c')
            ->whereNotNull('c.order_id')
            ->where([
                ['c.time', '>=', $this->now()->subHours(24)->toDateTimeString()],
                ['c.disposition', '=', 'no answer']
            ])
            ->join('orders as o', 'o.id', '=', 'c.order_id')
            ->pluck('o.key')
            ->toArray();

        $answered = array_unique($answered);
        $not_answered = array_unique($not_answered);
        $not_answered_strict = [];
        
        foreach ($not_answered as $n_order){
            if (!in_array($n_order, $answered)) {
                $not_answered_strict[] = $n_order;
            }
        }
        
        $not_answered = $not_answered_strict;
        
        $answered_orders = Order::whereIn('key', $answered)->get(['key', 'country_code'])->groupBy('country_code')->toArray();
        $not_answered_orders = Order::whereIn('key', $not_answered)->get(['key', 'country_code'])->groupBy('country_code')->toArray();

        $title = "Ежедневный отчёт по % прозвона \n";
        $date = date('Y-m-d H:i:s');
        $message = $title.$date."\n\n";

        foreach ($answered_orders as $geo_a => $orders_a){
            foreach ($not_answered_orders as $geo_n => $orders_n) {
                if($geo_a == $geo_n){
                    if((count($orders_a)+count($orders_n)) > 0){
                        $percent = round(100 * count($orders_a)/(count($orders_a)+count($orders_n)), 1);
                        $not_ans_keys = collect($orders_n)->pluck('key');
                        $temp_message = $geo_a."\n";
                        $temp_message .= "Дозв. - ".count($orders_a)."\n";
                        $temp_message .= "Не дозв. - ".count($orders_n)."\n";
                        $temp_message .= "% ".$percent."\n";

                        $file_link = $this->getCSV($orders_n);

                        $temp_message .= $file_link."\n\n";
                        $message .= $temp_message;
                    }
                    break;
                }
            }
        }

        $this->storeMessage($message);
    }

    private function getCSV($datum)
    {
        $dir = public_path("phoned_orders/");
        if(!is_dir($dir))
            mkdir($dir);
        $file_name = uniqid()."_not_answered.csv";
        $out = fopen($dir.$file_name, 'w+');
        $headers = array(
            'Content-Type' => 'text/csv',
        );

        $path = $dir.$file_name;
        $orders_url = env('FRONTEND_URL', '') . "/orders/";          // URL для заказов
        foreach ($datum as $data)
        {
            unset($data['country_code']);
            $data['key'] = $orders_url.$data['key'];
            fputcsv($out, $data,"\t");
        }
        fclose($out);
        response()->download($path, $file_name, $headers);
        $file_link = url("/phoned_orders/$file_name");

        return $file_link;
    }


    private function getManualCallsReport()
    {
        $title = "Отчёт по наличию ручного прозвона \n";
        $date = date('Y-m-d H:i:s');
        $message = $title.$date."\n\n";

        $orders_out = DB::table('orders as o')
            ->where([
                ['o.created_at', '>=', $this->now()->subDays(7)->toDateTimeString()],
                ['o.created_at', '<', $this->now()->subDays(3)->toDateTimeString()]
            ])
            ->join('calls as c', function ($jin) {
                $jin->on('c.order_id', '=', 'o.id')
                    ->where('c.call_type', 'out');
            })
            ->join('order_status as os', function ($jin) {
                $jin->on('os.order_id', '=', 'o.id')
                    ->where('os.status_type', 1);
            })
            ->select('o.id', 'o.country_code', 'os.status_id as current_1_group_status_id')
            ->get();
        $orders_out = collect($orders_out->unique('id')->values()->all())->groupBy('country_code')->toArray();

        foreach ($orders_out as $geo_key=>$geo_orders){
            $orders_count = DB::table('orders as o')->where([
                    ['created_at', '>=', $this->now()->subDays(7)->toDateTimeString()],
                    ['created_at', '<', $this->now()->subDays(3)->toDateTimeString()]
                ])->count();
            $orders_out_count = count($geo_orders);
            $completed = 0;
            $expected = 0;
            $trash = 0;
            $refused = 0;
            foreach ($geo_orders as $order){
                switch ($order->current_1_group_status_id){
                    case 17:
                        $completed++;
                        break;
                    case 18:
                        $expected++;
                        break;
                    case 19:
                        $refused++;
                        break;
                    case 58:
                        $trash++;
                        break;
                }
            }

            $message .= $geo_key."\n";
            if ($orders_count>0)
                $message .= round(100*$orders_out_count/$orders_count, 1)." %\n";
            // if($completed > 0)
                $message .= "Подтверждено: $completed\n";
            // if($expected > 0)
                $message .= "Ожидает: $expected\n";
            // if($refused > 0)
                $message .= "Отклонено: $refused\n";
            // if($trash > 0)
                $message .= "Мусор: $trash\n";

            $message .= "\n";
        }
        $this->storeMessage($message);
    }

    public function storeMessage($data)
    {
        Telegram::sendMessage([
            'chat_id' => env('TELEGRAM_CHANNEL_ID', '-1001316770748'),
            'parse_mode' => 'HTML',
            'text' => $data
        ]);
    }

}
