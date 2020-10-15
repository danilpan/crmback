<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\SmsService;

use App\Repositories\SmsRepository;
use App\Repositories\UnloadsRepository;
use App\Repositories\OrdersRepository;
use App\Repositories\GeoRepository;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

use Elasticsearch\Client as ElasticClient;
use App\Libraries\Mobizon_Error;

use App\Services\UnloadsService;

use App\Models\Order;

use App\Models\SmsRule;

use DB;

use Carbon\Carbon;

class SendAutoSms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SendAutoSms:set {--type=send}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Автоматическая рассылка СМС';

    // https://docs.google.com/document/d/1byiPHTikYOvBq-c7WDmIHhdtkRQtk6mHdLS4sE2GQ5A/edit
    // https://docs.google.com/document/d/1AxhUbTKQZ0h7j92EoWCg_x2t599zZtTXwvq6DdvAWPA/edit

    protected $smsService;
    protected $smsRepository;
    protected $unloadsRepository;
    protected $ordersRepository;
    protected $geoRepository;
    protected $client;
    protected $unloadsService;

    private $kz_numbers = ' По всем вопросам: 77222550870, 77222550874'; 
    private $sms_type = null; 

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        SmsService $smsService,                          
        SmsRepository $smsRepository,
        UnloadsRepository $unloadsRepository,
        GeoRepository $geoRepository,
        OrdersRepository $ordersRepository,
        ElasticClient $client,
        UnloadsService $unloadsService
    )
    {
        parent::__construct();
        $this->smsService = $smsService;                                           
        $this->smsRepository = $smsRepository;   
        $this->unloadsRepository = $unloadsRepository;   
        $this->geoRepository = $geoRepository;
        $this->ordersRepository = $ordersRepository;   
        $this->client = $client;   
        $this->unloadsService = $unloadsService;   
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

/*        $smss = [
            ['id'=>1,'type'=>5,'created_at'=>'2019-05-03 14:00:31'],
            ['id'=>2,'type'=>8,'created_at'=>'2019-05-03 14:00:31'],
            ['id'=>3,'type'=>5,'created_at'=>'2019-05-08 14:00:31'],
            ['id'=>4,'type'=>8,'created_at'=>'2019-05-10 14:00:31'],
            ['id'=>5,'type'=>5,'created_at'=>'2019-05-15 14:00:31']
        ];

        $smss_by_type = array_filter($smss, function($sms){       
            if($sms['type']==8)return $sms;
        }); 

        $lastSmsTime       = end($smss_by_type);
        $lastSmsTimeFormat = new Carbon($lastSmsTime['created_at']);
        $lastSmsTimeFormat = $lastSmsTimeFormat->format('Y-m-d');
        //$nowDateFormat = Carbon::now()->format('Y-m-d');
        $nowDateFormat = Carbon::now();
        //$difference        = round(( strtotime( $nowDateFormat) - strtotime( $lastSmsTimeFormat ) )/(60*60*24));

        dd($nowDateFormat->diffInDays($lastSmsTimeFormat));

        foreach ($smss as $sms) {
            $smss_by_type = [];


            $sms_time = new Carbon($sms['created_at']);
            $sms_time = $sms_time->format('Y-m-d');                
            if(in_array($sms_time,$intervals) && $sms['type']==5){
                break;
            }
        }*/




        /*$add_list = [];
$add_filter = []; 

        //$add_filter[] = ["status_3.created_at",">",Carbon::now()->subDay()->format('Y/m/d 00:00:00')];     

        $api_key = '6b0e3762a0493ee468300938e184a9bd';

        $orders = $this->unloadsService->getOrdersByAPIKey($api_key, 0, 5000, $add_filter, [], $add_list, []);
        dd();
DB::enableQueryLog();*/


/*$response = $this->client->get([
    'index' => 'orders',
    'type' => 'orders',
    'id' => '837912'
]);
dd( $response);

dd();*/

        $type     = $this->option('type');

        $orders = [];

        try {
            if($type == 'package_send'){
                $orders = $this->package_send();
            }elseif($type == 'package_arrived'){
                $orders = $this->package_arrived();
            }elseif($type == 'after_certain_days'){
                $orders = $this->after_certain_days();
            }elseif($type == 'smssender_send_sms'){
                $orders = $this->smssender_send_sms();
            }elseif($type == 'smssender_send_pre_pay_sms'){
                $orders = $this->smssender_send_pre_pay_sms();
            }elseif($type == 'status_3_4_KzKrg_11'){
                $orders = $this->status_3_4_kzkrg(11);
            }elseif($type == 'status_3_4_KzKrg_12'){
                $orders = $this->status_3_4_kzkrg(12);
            }elseif($type == 'status_3_4_KzKrg_13'){
                $orders = $this->status_3_4_kzkrg(13);
            }elseif($type == 'status_3_4_KzKrg_14'){
                $orders = $this->status_3_4_kzkrg(14);
            }
        }catch (\Throwable $e) {                                
            echo 'Выброшено исключение црм: ',  $e->getMessage(), "\n";
            $log = ['errorCrm' => $e->getMessage()];
            $orderLog = new Logger('sms');
            $orderLog->pushHandler(new StreamHandler(storage_path('logs/sms.log')), Logger::INFO);
            $orderLog->info('SmsLog', $log);
        }
        $orders_new = [];        
        

        foreach ($orders as $order) {                        
            if(!isset($order['geo']))dd($order);
            $orders_new[$order['organization_id']][$order['geo']['id']][] = $order;
        }

        $iter = 1;
        foreach ($orders_new as $org_id => $geo_arr) {            
            foreach ($geo_arr as $geo_id => $leads) {
                foreach ($leads as $key => $lead) {
                    echo $iter.' - '.$org_id.' - '.$geo_id.' - '.$lead['id'].' - '.$lead['sms_message'].PHP_EOL;
                    $iter++;
                }                
            }
        }                
        
        foreach ($orders_new as $organization_id => $geo_orders) {           
            foreach ($geo_orders as $geo => $orders_send) {
                $smsRules = null;
                $query = [];
                $query['organization_id'] = $organization_id;
                $query['is_work'] = 1;     
                $query['type'] = 2;     
                $query['geo_id'] = $geo;     
                $smsRules = SmsRule::where($query)->with('sms_provider')->get()->first();                    
                if($smsRules){
                    $smsRules->toArray();
                    $provider = null;
                    $api_key = null;                                       
                    if(isset($smsRules['sms_provider']) && !empty($smsRules['sms_provider'])){                        
                        if($smsRules['sms_provider']['sms_provider']==1){
                            $provider = $this->smsService->getMobizon($smsRules['sms_provider']);                    
                            $api_key = '';
                        }elseif($smsRules['sms_provider']['sms_provider']==2){
                            $provider = $this->smsService->getSmsc($smsRules['sms_provider']);                    
                            $api_key = [];
                        }
                        if($provider){
                            try {
                                $result = $provider->bulkSending($api_key, $orders_send, $this->sms_type);                   
                                if($result){
                                    $orders_temp_refresh = [];
                                    foreach ($orders_send as $order_refresh) {
                                        $orders_temp_refresh[$order_refresh['id']] = $order_refresh['id'];
                                    }
                                    $orders_refresh = Order::whereIn('id',$orders_temp_refresh)->get();       
                                    $this->ordersRepository->reindexByData($orders_refresh);
                                }
                            }catch (\Throwable $e) {                                
                                echo 'Выброшено исключение црм: ',  $e->getMessage(), "\n";
                                $log = ['errorCrm' => $e->getMessage()];
                                $orderLog = new Logger('sms');
                                $orderLog->pushHandler(new StreamHandler(storage_path('logs/sms.log')), Logger::INFO);
                                $orderLog->info('SmsLog', $log);
                            }catch (Mobizon_Error $e) {                                
                                echo 'Выброшено исключение Mobizon: ',  $e->getMessage(), "\n";
                                $log = ['errorMobizon' => $e->getMessage()];
                                $orderLog = new Logger('sms');
                                $orderLog->pushHandler(new StreamHandler(storage_path('logs/sms.log')), Logger::INFO);
                                $orderLog->info('SmsLog', $log);
                            }                                                        
                                                                     
                        }
                    }                    
                }                
                //var_dump($smsRules);                                
            }         
            
        }        

        dd(count($orders));      

        
    }  

    public function status_3_4_kzkrg($method = null){
        if(empty($method))return [];            

        $hour = (int)date('H');

        if ($hour >= 19) {
            dd('Нерабочее время');   
        }

        if ($hour < 6) {
            dd('Нерабочее время');   
        }           

        $filter['must']['bool']['must'][]['wildcard']['status_1c_3'] = 'True'; //есть реализация

        $filter['must']['bool']['must'][]['bool']['must'] = [
                        'exists'=>[
                            'field' => 'track_number'
                        ]
                    ];//есть трек-номер

        $filter['must']['bool']['must'][]['bool']['must'] = [
                        'exists'=>[
                            'field' => 'geo.id'
                        ]
                    ];//есть гео

        $filter['must']['bool']['must_not'][]['term']['sms.type'] = $method; // Не было подобных смс

        $this->sms_type = $method;

        if($method==14){            
            $filter['must']['bool']['must'][]['term']['status_3.id'] = 77; //Ожидаем выкупа
            $filter['must']['bool']['must'][]['term']['status_4.id'] = 42; //Недозвон      

            $filter['must']['bool']['must'][]['bool']['should'] = $this->get_filters([114,15], 'delivery_types_id'); // Типы доставок   
            $filter['must']['bool']['must'][]['range']['status_4.created_at'] = ['gt'=>Carbon::now()->subDays(2)->format('Y/m/d 00:00:00')]; //За последние два дня          
        }

        if($method==13){
            $filter['must']['bool']['must'][]['term']['status_3.id'] = 77; //Ожидаем выкупа
            $filter['must']['bool']['must'][]['term']['status_4.id'] = 40; //Обещает забрать

            $filter['must']['bool']['must'][]['bool']['should'] = $this->get_filters([114,15], 'delivery_types_id'); // Типы доставок              
            
            $filter['must']['bool']['must'][]['range']['status_4.created_at'] = ['gt'=>Carbon::now()->subDays(2)->format('Y/m/d 00:00:00')]; //За последние два дня          
        }

        if($method==12){
            $filter['must']['bool']['must'][]['term']['status_3.id'] = 77; //Ожидаем выкупа
            $filter['must']['bool']['must'][]['bool']['should'] = [
                [
                    'bool'=>[
                        'must_not'=>[
                            'exists'=>[
                                'field' => 'status_4.id'
                            ]
                        ]
                    ]
                ]
            ];//Нет 4-й группы статусов                  

            $filter['must']['bool']['must'][]['bool']['should'] = $this->get_filters([114], 'delivery_types_id'); // Типы доставок   
            $filter['must']['bool']['must'][]['range']['status_3.created_at'] = ['gt'=>Carbon::now()->subDays(2)->format('Y/m/d 00:00:00')]; //За последние два дня          
        }

        if($method==11){                        
            $filter['must']['bool']['must'][]['bool']['should'] = $this->get_filters([114], 'delivery_types_id'); // Типы доставок   
            $filter['must']['bool']['must'][]['range']['status_1c_3_time'] = ['gt'=>Carbon::now()->subDays(2)->format('Y-m-d 00:00:00')]; //За последние два дня
        }

        //dd($filter);

        $query['constant_score']['filter']['bool']['must'] = $filter['must'];        

        $data = $this->ordersRepository->searchByParamsScroll(
            $query, 
            ['id'=>'asc'],0,10000,false)->toArray();       

        $orders = [];        

        //Добавление текта сообщения
        $orders = array_map(function($item) use($method){
            if($method==14)$item['sms_message'] = "Здравствуйте! С момента поступления Вашего заказа в отделение почты, мы долгое время не могли с Вами связаться. У Вас появилась грандиозная возможность выиграть APPLE MACBOOK PRO и 10 промежуточных призов! Розыгрыш призов проводится каждую субботу, в 18.00. желаем удачи!";
            if($method==13)$item['sms_message'] = "Здравствуйте! Срок хранения посылки истекает, спешите забрать Ваш заказ! У Вас есть грандиозная возможность выиграть APPLE MACBOOK PRO и 10 промежуточных призов! Розыгрыш призов проводится каждую субботу, в 18.00. желаем удачи!";
            if($method==12)$item['sms_message'] = "Здравствуйте! Уведомляем о том что Ваш заказ прибыл в отделение Казпочты, трек номер заказа ".$item['track_number'].". Напоминаем, что выкупив заказ Вы становитесь участником розыгрыша Noutbuka HP и 10 промежуточных призов! Желаем удачи!";
            if($method==11)$item['sms_message'] = "Добрый день! Ваш заказ отправлен, отследить посылку Вы можете с помощью трек номера ".$item['track_number']." на сайте казпочты - Post.kz. Напоминаем, что выкупив заказ Вы становитесь участником розыгрыша ноутбука HP и 10 промежуточных призов! Желаем удачи!";
            $item['sms_message'] = $item['sms_message'].$this->kz_numbers;
            return $item;
        }, $data);

        return $orders;

    }

    public function get_filters($data, $field){
        $result = [];
        foreach ($data as $value) {
            $result[] = [
                        'term'=>[$field=>$value]
                    ];
        }
        return $result;
    }

    public function smssender_send_pre_pay_sms(){ 

        $filter['must']['bool']['must'][]['term']['status_1.id'] = 17; //подтвержден        
        $filter['must']['bool']['must'][]['term']['organization_id'] = 67; //Организация Белкин           
        $filter['must']['bool']['must'][]['wildcard']['country_code'] = 'RU'; //Гео RU
        $filter['must']['bool']['must'][]['bool']['should'] = [
            [
                'bool'=>[
                    'must_not'=>[
                        'exists'=>[
                            'field' => 'status_5.id'
                        ]
                    ]
                ]
            ],[
                'term'=>['status_5.id'=>103]
            ]
        ];  //Или На проверке или совсем нет        

        $filter['must']['bool']['must'][]['bool']['must'] = [
                        'exists'=>[
                            'field' => 'geo.id'
                        ]
                    ];//есть гео

        $filter['must']['bool']['must'][]['range']['status_1.created_at'] = ['gt'=>Carbon::now()->subDays(13)->format('Y/m/d 00:00:00')]; //Не новее 13 дней - дата подтверждения        

        $filter['must']['bool']['must_not'][]['bool']['should'] = $this->get_filters([1386,1385,341,1377,79,1374,1372,1371,5398], 'projects.id'); // //Не перечисленные проекты


        $query['constant_score']['filter']['bool']['must'] = $filter['must'];

        $data = $this->ordersRepository->searchByParamsScroll(
            $query, 
            ['id'=>'asc'],0,10000,false)->toArray();      

        $this->sms_type = 8;

        //dd($data);

        $orders = [];

        //Проверка на отправку
        $orders = array_filter($data, function($item){       
            $flag = true;                 
            $smss_by_type = [];
            $cost_main = $item['delivery_types_price'];
            foreach ($item['sales'] as $sale) {
                $cost_main += $sale['quantity'] * $sale['price'];
            }
            if($cost_main < 13000)$flag = false;     //Стоимость заказа не ниже 13000 рублей             

            if($flag){                
                //Только смс нужного типа
                $smss_by_type = array_filter($item['sms'], function($sms){       
                    if($sms['type']==8)return $sms;
                }); 

                //Кол-во смс нужного типа
                $count_smss_by_type = count($smss_by_type);
                
                //Если смс нужного типа больше либо 6 - не отсылаем
                if($count_smss_by_type>=6){
                    $flag = false;
                }else{
                    if($count_smss_by_type!=0){ //Если смс нужного типа были
                        $lastSmsTime       = end($smss_by_type); //Последняя смс нужного типа
                        $lastSmsTimeFormat = new Carbon($lastSmsTime['created_at']); 
                        $lastSmsTimeFormat = $lastSmsTimeFormat->format('Y-m-d');//Дата последней смс нужного типа
                        
                        $nowDateFormat = Carbon::now(); // Сегодня

                        $difference = $nowDateFormat->diffInDays($lastSmsTimeFormat);//Разница между сегодня и датой последней смс нужного типа                        
                        
                        //Сопостовление кол-ва смс и разницей в днях с Сегодня
                        if(in_array($count_smss_by_type, [1,2,3])){
                            if ($difference!=1) {
                                $flag = false;   
                            }
                        }
                        if(in_array($count_smss_by_type, [4,5])){
                            if ($difference!=2) {
                                $flag = false;   
                            }
                        }                        
                    }
                }
            }


            if($flag)return $item;    
        }); 

        $orders = array_map(function($item){            
            $item['sms_message'] = 'Киви-кошелёк для предоплаты: +79616345253 Сумма: 480р. В комментарии платежа укажите номер своего заказа '.$item['id'].'. После оплаты ОБЯЗАТЕЛЬНО выслать фото квитанции на электронную почту: Egor9355@yandex.ru В теме письма укажите фамилию и номер телефона на кого оформлен заказ!';
            return $item;
        }, $orders);

        return $orders;
    }
   
    public function smssender_send_sms(){       
        dd('Нужно установить ID доставок');
        $filter['must']['bool']['must'][]['term']['status_1.id'] = 17; //подтвержден
        $filter['must']['bool']['must_not'][]['term']['status_2.id'] = 20; //Не установлен - деньги получены в кассу
        $filter['must']['bool']['must'][]['term']['status_3.id'] = 77; //Ожидает выкупа  

        $filter['must']['bool']['must_not'][]['term']['sms.type'] = 7; // Не было подобных смс

        $filter['must']['bool']['must_not'][]['bool']['should'] = $this->get_filters([39,212,202,213,203,214,204,205,206,207,208,209,210,211], 'status_4.id'); // Исключение статусов 4-й группы        

        $filter['must']['bool']['must'][]['bool']['should'] = $this->get_filters([83,84,210,213,284,85,86,87,76,58], 'delivery_types_id');  // Типы доставок         

        $filter['must']['bool']['must'][]['range']['status_3.created_at'] = ['lt'=>Carbon::now()->subDays(10)->format('Y/m/d 00:00:00')]; //Не новее 10 дней - дата логистики
        $filter['must']['bool']['must'][]['range']['status_3.created_at'] = ['gt'=>Carbon::now()->subDays(30)->format('Y/m/d 00:00:00')]; //Не старее 30 дней - дата логистики

        $query['constant_score']['filter']['bool']['must'] = $filter['must'];

        $filter['must']['bool']['must'][]['bool']['must'] = [
                        'exists'=>[
                            'field' => 'geo.id'
                        ]
                    ];//есть гео

        $this->sms_type = 7;

        $data = $this->ordersRepository->searchByParamsScroll(
            $query, 
            ['id'=>'asc'],0,10000,false)->toArray();      

        $orders = [];        

        $orders = array_map(function($item){            
            $item['sms_message'] = 'Уважаемый ' . $item['client_name']. '! Поздравляем! Вы заказывали у нас "' . $item['projects'][0]['name_for_client'] . '". Один из 4 Iphone 7 ждет Вас! Получение своего Приза и подробности - по ссылке: http://iphone7win.ru/'. (string)$item['key'];
            return $item;
        }, $data);

        return $orders;
    }



    public function after_certain_days(){       

        $daysInterval = [
            3, 
            5, 
            10, 
            15, 
            20,
        ]; //Интервал по которым происходит отправление СМС

        $filter['must']['bool']['must'][]['term']['status_1.id'] = 17; //подтвержден
        $filter['must']['bool']['must'][]['term']['status_5.id'] = 36; //не фрод
        $filter['must']['bool']['must'][]['term']['status_3.id'] = 77; //Ожидает выкупа     

        $filter['must']['bool']['must_not'][]['bool']['should'] = $this->get_filters([39,38,40,170,122], 'status_4.id');  // Исключение статусов 4-й группы -Отказ опишу в комменте, Говорит забрал(а), Обещает забрать, Заказ на удаление, Брак  

        $filter['must']['bool']['must'][]['bool']['should'] = $this->get_filters([114], 'delivery_types_id');  // Типы доставок Казпочта хис,Казпочта 2,Казпочта 3         
        
        $filter['must']['bool']['must'][]['range']['status_3.created_at'] = ['gt'=>Carbon::now()->subDays(30)->format('Y/m/d 00:00:00')]; //За последние 30 дней - дата логистики

        $filter['must']['bool']['must'][]['bool']['must'] = [
                        'exists'=>[
                            'field' => 'geo.id'
                        ]
                    ];//есть гео

        $query['constant_score']['filter']['bool']['must'] = $filter['must'];

        $data = $this->ordersRepository->searchByParamsScroll(
            $query, 
            ['id'=>'asc'],0,10000,false)->toArray();

        $orders = [];

        $this->sms_type = 6;

        //Проверка на интервал
        $orders = array_filter($data, function($item) use($daysInterval){                                    
            
            $flag = true;

            $date_status_3 = new Carbon($item['status_3']['created_at']); //Дата отдела логистига

            $date_status_3_interval = [];            

            //Даты, согласно интервалу, когда нужна отправка СМС
            foreach ($daysInterval as $day) {
                $date_status_3_interval[] = $date_status_3->copy()->addDays($day)->format('Y-m-d');                    
            }

            $now = Carbon::now()->format('Y-m-d'); //Сегодня                       
            
            if(in_array($now, $date_status_3_interval)){ //Входит ли Сегодня в массив дат, согласно интервалу
                foreach ($item['sms'] as $sms) {
                    $sms_time = new Carbon($sms['created_at']);
                    $sms_time = $sms_time->format('Y-m-d');                
                    if($sms_time==$now && $sms['type']==6){ // Если сегодня уже была отправка текущей рассылки то пропускаем этот заказ
                        $flag = false;
                        break;
                    }
                }
            }else{
                $flag = false;
            }
                
            if($flag)return $item;                
                        
        }); 

        $orders = array_map(function($item){            
            $item['sms_message'] = 'Добрый день! Напоминаем, что Ваш заказ находится в отделении казпочты. Успейте выкупить до истечения срока хранения и стать участником розыгрыша одного из 4 Iphone X.'.$this->kz_numbers;
            return $item;
        }, $orders);

        return $orders;
    }

    public function package_arrived(){

        $filter['must']['bool']['must'][]['term']['status_1.id'] = 17; //подтвержден
        $filter['must']['bool']['must'][]['term']['status_5.id'] = 36; //не фрод
        $filter['must']['bool']['must'][]['term']['status_3.id'] = 77; //Ожидает выкупа

        $filter['must']['bool']['must_not'][]['term']['sms.type'] = 5; // Не было подобных смс

        $filter['must']['bool']['must'][]['bool']['should'] = [
            [
                'bool'=>[
                    'must_not'=>[
                        'exists'=>[
                            'field' => 'status_4.id'
                        ]
                    ]
                ]
            ]
        ];//Нет 4-й группы статусов        

        $filter['must']['bool']['must'][]['bool']['must'] = [
                        'exists'=>[
                            'field' => 'geo.id'
                        ]
                    ];//есть гео

        $filter['must']['bool']['must'][]['bool']['should'] = $this->get_filters([114], 'delivery_types_id');  // Типы доставок Казпочта хис,Казпочта 2,Казпочта 3                 

        $filter['must']['bool']['must'][]['range']['status_3.created_at'] = ['gt'=>Carbon::now()->subDay()->format('Y/m/d 00:00:00')]; //За последний день - дата логистики

        $query['constant_score']['filter']['bool']['must'] = $filter['must'];

        $data = $this->ordersRepository->searchByParamsScroll(
            $query, 
            ['id'=>'asc'],0,10000,false)->toArray();      

        $orders = [];

        $this->sms_type = 5;        

        $orders = array_map(function($item){            
            $item['sms_message'] = 'Ваш заказ прибыл на Казпочту. Трек номер посылки (' . $item['track_number'] . '). Напоминаем, что Вы участвуете в праздничном розыгрыше  Iphone X ! Один из 4 Iphone X ждут Вас!'.$this->kz_numbers;
            return $item;
        }, $data);

        return $orders;
    }

    public function package_send(){                
        dd('Не работает');
        $filter['must']['bool']['must'][]['term']['status_1.id'] = 17; //подтвержден
        $filter['must']['bool']['must'][]['term']['status_5.id'] = 36; //не фрод
        $filter['must']['bool']['must_not'][]['term']['sms.type'] = 4; // Не было подобных смс

        $filter['must']['bool']['must'][]['bool']['should'] = [
            [
                'bool'=>[
                    'must_not'=>[
                        'exists'=>[
                            'field' => 'status_4.id'
                        ]
                    ]
                ]
            ]
        ];//Нет 4-й группы статусов

        $filter['must']['bool']['must'][]['bool']['must'] = [
                        'exists'=>[
                            'field' => 'geo.id'
                        ]
                    ];//есть гео

        $filter['must']['bool']['must'][]['bool']['should'] = $this->get_filters([22,114,115], 'status_3.id');  // 3-я группа статусов равно В пути,Передан на почту,Получен трекинг     

        $filter['must']['bool']['must'][]['bool']['should'] = $this->get_filters([], 'delivery_types_id');  // Типы доставок Казпочта хис и мл        

        $filter['must']['bool']['must'][]['range']['status_3.created_at'] = ['gt'=>Carbon::now()->subDay()->format('Y/m/d 00:00:00')]; //За последний день - дата логистики

        $query['constant_score']['filter']['bool']['must'] = $filter['must'];

        $data = $this->ordersRepository->searchByParamsScroll(
            $query, 
            ['id'=>'asc'],0,10000,false)->toArray();      

        $orders = [];

        $this->sms_type = 4;         

        $orders = array_map(function($item){            
            $item['sms_message'] = 'Ваш заказ отправлен!Трек-номер для отслеживания(' . $item['track_number'] . ').Если заберете посылку в течении недели, Вы автоматически становитесь участником розыгрыша  Iphone X и десяти промежуточных призов.'.$this->kz_numbers;
            return $item;
        }, $data);

        return $orders;
    }      

}
