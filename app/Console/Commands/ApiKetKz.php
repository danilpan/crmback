<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use DB;

use App\Models\Order;
use App\Models\OrderDeliveryInfo;
use App\Models\History;
use App\Repositories\OrdersRepository;
use Carbon\Carbon;


class ApiKetKZ extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ApiKetKZ:set {--type=send}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Апи со службой доставки KetKZ';

    private $uid = array("KZ" => "38490424",
                         "KG" => "62357225",
                         "ALMTK" => "88264578"
                    ); 
    private $secret = array("KZ" => "P10Ny96S",
                            "KG" => "GU0Ek3xN",
                            "ALMTK" => "LRhMKX1q",
                    );

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        OrdersRepository $ordersRepository
    )
    {
        $this->ordersRepository = $ordersRepository;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {        

        $type = $this->option('type');    

        if($type == 'send'){
            $this->send();
        }else if($type == 'check'){
            $this->check();
        }
    }


    public function send(){        
        $deliveryCrmStr = $this->get_crm_kz_delivery();        
        //$deliveryCrmStr = array_diff($deliveryCrmStr, [209,226,227,228]);                 
        $deliveryCrmStrKg = $this->get_crm_kg_delivery();   
        
        $ketOrders = DB::table('orders as o')
        ->select('o.id','o.key','o.postcode','o.region','o.area','o.city','o.street','o.home','o.room','o.phones','o.client_name','o.delivery_types_id','o.delivery_types_price','o.delivery_date_finish','o.delivery_time_1','o.delivery_time_2','o.country_code','o.surplus_percent_price','sales.id as sale_id','sales.name as sale_name','sales.quantity','sales.product_id','sales.price','status_1.status_id as status_1','status_2.status_id as status_2')
        ->whereIn('o.delivery_types_id', $deliveryCrmStr)        
        ->where([
            ['o.status_1c_3', '=', 'True']
        ])
        ->join('sales', 'sales.order_id', '=', 'o.id')
        ->join('order_status as status_1', function($join) {
            $join->on('status_1.order_id', '=', 'o.id')            
            ->where('status_1.status_id', '=', 17);
        })
        ->join('order_status as status_2', function($join) {
            $join->on('status_2.order_id', '=', 'o.id')            
            ->where('status_2.status_id', '=', 36);
        })
        ->leftJoin('order_delivery_info as di', function ($join){
            $join->on('di.order_id', '=', 'o.id')
            ->where('di.d_type', '=', 'ketkz');                 
        })
        ->whereNull('di.order_id')        
        ->orderBy('o.id','desc')        
        ->get()
        ->toArray(); 

        $errorOrders = DB::table('orders as o')
        ->select('o.id','o.key','o.postcode','o.region','o.area','o.city','o.street','o.home','o.room','o.phones','o.client_name','o.delivery_types_id','o.delivery_types_price','o.delivery_date_finish','o.delivery_time_1','o.delivery_time_2','o.country_code','o.surplus_percent_price','sales.id as sale_id','sales.name as sale_name','sales.quantity','sales.product_id','sales.price','status_1.status_id as status_1')          
        ->join('sales', 'sales.order_id', '=', 'o.id')
        ->join('order_status as status_1', function($join) {
            $join->on('status_1.order_id', '=', 'o.id')            
            ->where('status_1.status_id', '=', 17);
        })        
        ->join('order_delivery_info as di', function ($join){
            $join->on('di.order_id', '=', 'o.id')
            ->where([['di.d_type', '=', 'ketkz'],['di.is_error', '=', 1]]);                 
        })                
        ->orderBy('o.id','desc')        
        ->get()
        ->toArray();                   

        $ketOrdersKrg = DB::table('orders as o')
        ->select('o.id','o.key','o.postcode','o.region','o.area','o.city','o.street','o.home','o.room','o.phones','o.client_name','o.delivery_types_id','o.delivery_types_price','o.delivery_date_finish','o.delivery_time_1','o.delivery_time_2','o.country_code','o.surplus_percent_price','sales.id as sale_id','sales.name as sale_name','sales.quantity','sales.product_id','sales.price','status_1.status_id as status_1','status_2.status_id as status_2')
        ->whereIn('o.delivery_types_id', $deliveryCrmStrKg)        
        ->join('sales', 'sales.order_id', '=', 'o.id')
        ->join('order_status as status_1', function($join) {
            $join->on('status_1.order_id', '=', 'o.id')            
            ->where('status_1.status_id', '=', 17);
        })
        ->join('order_status as status_2', function($join) {
            $join->on('status_2.order_id', '=', 'o.id')            
            ->where('status_2.status_id', '=', 36);
        })
        ->leftJoin('order_delivery_info as di', function ($join){
            $join->on('di.order_id', '=', 'o.id')
            ->where('di.d_type', '=', 'ketkz');                 
        })
        ->whereNull('di.order_id')        
        ->orderBy('o.id','desc')        
        ->get()
        ->toArray();

        $ketOrders = $this->re_construct($ketOrders);
        $errorOrders = $this->re_construct($errorOrders);
        $ketOrdersKrg = $this->re_construct($ketOrdersKrg);

        if((count($ketOrders)==0) && (count($errorOrders)==0) && (count($ketOrdersKrg)==0)) exit('Нет заказов'); //если пусто пропускаем

        $ketLeads = array_merge($ketOrders, $errorOrders);     
        $ketLeads = array_merge($ketLeads, $ketOrdersKrg);             

        foreach ($ketLeads as $lead) {                        
            $leadArr = array();
            $addressArr = array();
            $date_delivery = '';
            $orderInfo = array();

            $secret = $this->secret[$lead['country_code']];
            $uid = $this->uid[$lead['country_code']];

            $almtk_type = false;

            /*if(in_array($lead['delivery_types_id'],array(209,226,227,228))){
                $secret = $this->secret['ALMTK'];
                $uid = $this->uid['ALMTK'];             
                $almtk_type = true;
            }*/
            
            if(!empty($lead['region']))$addressArr[]=$lead['region'];
            if(!empty($lead['area']))$addressArr[]=$lead['area'];
            if(!empty($lead['city']))$addressArr[]=$lead['city'];
            if(!empty($lead['street']))$addressArr[]=$lead['street'];
            if(!empty($lead['home']))$addressArr[]=$lead['home'];
            if(!empty($lead['room']))$addressArr[]=$lead['room'];
            
            if(!empty($lead['delivery_date_finish']))$date_delivery = (date('Y-m-d',strtotime($lead['delivery_date_finish']))).' '.$lead['delivery_time_2'];

            $leadsProdsArr = [];
            $leadsProdsArrCyr = [];

            foreach ($lead['sales'] as $sale) {
                $leadsProdsArr[] = $sale['en_name'].' - '.$sale['quantity'];
                $leadsProdsArrCyr[] = $sale['name'].' - '.$sale['quantity'];
            }

            $offers = implode(', ',$leadsProdsArr);
            $offers_cyr = implode(', ',$leadsProdsArrCyr);

            $phones = json_decode($lead['phones']);
            
            $leadArr['phone'] = $phones[0];
            if(isset($phones[1]))$leadArr['phone_sms'] = $phones[1];
            $leadArr['price'] = $lead['cost_main'];
            //$leadArr['order_id'] = $lead['id'];
            $leadArr['order_id'] = $lead['key'];
            $leadArr['name'] = $lead['client_name'];
            $leadArr['country'] = mb_strtolower($lead['country_code']);
            $leadArr['index'] = $lead['postcode'];
            $leadArr['addr'] = implode(', ',$addressArr);
            $leadArr['status'] = "1";
            $leadArr['kz_delivery'] = $this->get_delivery($lead['delivery_types_id']);
            $leadArr['offer'] = $offers;
            $leadArr['sale_option'] = $offers_cyr;
            $leadArr['secret'] = $secret;
            $leadArr['description'] = "";
            $leadArr['date_delivery'] = $date_delivery;         
            $leadArr['is_dvd'] = '';
            
            $data = $leadArr;                   
            
            print_r($leadArr);            
            
            $data = json_encode($data);
            $hash_str = strlen($data) . md5($uid);
            $hash = hash("sha256", $hash_str); 
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "http://ketkz.com/api/send_order.php?uid=" . $uid . "&hash=" . $hash);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, array("data" => $data));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Pragma: no-cache"));
            $result = curl_exec($ch);     

            if(empty($result)){
                $result = '{"result":{"success":"FALSE","message":"Нет ответа от сервера"}}';   
            }

            $resultArr = json_decode($result, true);

            print_r($resultArr);            
                        
            $orderInfo = [];
            $orderInfo['order_id'] = $lead['id'];
            $orderInfo['d_type'] = 'ketkz';
            $orderInfo['is_work'] = '1';
            $orderInfo['type'] = '0';
            if($lead['country_code']=='KG')$orderInfo['type'] = '1';
            if($almtk_type)$orderInfo['is_type'] = '2';            
                
            if($resultArr['result']['success']=="TRUE" && isset($resultArr['result']['id'])){
                $orderInfo['status'] = '';
                $orderInfo['is_error'] = '0';
                $orderInfo['delivery_id'] = $resultArr['result']['id'];
            }else{              
                $orderInfo['is_error'] = '1';
                $orderInfo['status'] = 'Ошибка: '.$resultArr['result']['message'];
            }           

            OrderDeliveryInfo::updateOrCreate(
                ['order_id' => $orderInfo['order_id'], 'd_type' => $orderInfo['d_type']],
                $orderInfo
            );    
        }
    }

    public function check(){        
        $geos = ["0","1","2"]; //0-KZ, 1-KG, 2-ALMTK
        foreach ($geos as $geo) {
            $ketOrders = DB::table('order_delivery_info as di')
            ->select(
                'di.id',
                'di.order_id',
                'di.delivery_id',
                'di.status_id as dl_status_id',
                'di.status as dl_status',
                'di.s_status',
                'di.type',
                'status_3.status_id as status_group_3',
                'orders.key')
            ->where('di.d_type','ketkz')
            ->where('di.is_work', 1)                                    
            ->where('di.is_error', 0)                                    
            ->where('di.type', $geo)                             
            ->leftJoin('order_status as status_3', function($join) {
                $join->on('status_3.order_id', '=', 'di.order_id')            
                ->where('status_3.status_type', '=', 3);
            })            
            ->leftJoin('orders', 'orders.id', '=', 'di.order_id')                        
            ->orderBy('di.id','desc')            
            ->get()
            ->toArray();                
            

            if(count($ketOrders)==0) continue; //если пусто пропускаем            
            
            //Дробим заказы на максимальное возможное количество запросов за один раз

            $ketOrders = array_chunk($ketOrders, 500);
            $iter = 0;
            $updateIter = 1;
            $new_collection = [];
        
            do{
                
                $data = array();
                
                $checkArray = array();

                $keys = [];
                            
                foreach ($ketOrders[$iter] as $lead)
                {                                           
                    
                    $lead = (array)$lead;
                    $keys[$lead['key']] = $lead['order_id'];
                    if(!empty($lead['delivery_id']))
                        $data[] = ["id" => $lead['delivery_id']];

                    $checkArray[$lead['order_id']]['status'] = $lead['status_group_3'];                    
                    $checkArray[$lead['order_id']]['dl_status'] = $lead['dl_status'];
                    $checkArray[$lead['order_id']]['dl_status_id'] = $lead['dl_status_id'];
                    $checkArray[$lead['order_id']]['dl_rus_post_status'] = $lead['s_status'];
                }                     
                
                //echo json_encode($data);      
                if($geo=="0")$key="KZ";
                if($geo=="1")$key="KG";
                if($geo=="2")$key="ALMTK";
                 
                $data = json_encode($data);

                $hash_str = strlen($data) . md5($this->uid[$key]);
                $hash = hash("sha256", $hash_str); 
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "http://ketkz.com/api/get_orders.php?uid=" . $this->uid[$key] . "&s=" . $this->secret[$key] . "&hash=".$hash);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, array("data" => $data));
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array("Pragma: no-cache"));
                $result = curl_exec($ch);                              
               
                $resultArr = json_decode($result, true);                               
                
                //Сопоставление статусов ketkz к статусам crmk'и
                $statusKetToCrm = [
                    "0" => ["title" => "Отправлен", "status_group_3" => "166"],
                    "4" => ["title" => "Отказ", "status_group_3" => "89"],
                    "5" => ["title" => "Оплачен", "status_group_3" => "26"],
                    "6" => ["title" => "На отправку", "status_group_3" => "107"],
                    "7" => ["title" => "Отклонено", "status_group_3" => "196"]
                ];       
                
                foreach ($resultArr as $order){

                    echo '<pre>';
                    print_r($order);
                    echo '</pre>';
                    
                    $dataInfo = [];
                    $leadInfo = [];

                    if(isset($keys[$order['ext_id']])){
                        $lead_id = $keys[$order['ext_id']];            
                        
                        if($order['send_status'] == "5" || $order['send_status'] == "4" || $order['send_status'] == "7"){
                          $dataInfo['is_work'] = '0';             
                        }                                  
                        
                        if(($checkArray[$lead_id]['dl_status'] != $statusKetToCrm[$order['send_status']]['title'])
                         || ($checkArray[$lead_id]['dl_status_id'] != $order['send_status'])
                         || ($checkArray[$lead_id]['dl_rus_post_status'] != $order['status_kz'])
                         || (isset($dataInfo['is_work']) && $dataInfo['is_work'] == '0')){                        
                            
                            $dataInfo['status_id'] = $order['send_status']; 
                            $dataInfo['status'] = $statusKetToCrm[$order['send_status']]['title'];  
                            $dataInfo['s_status'] = $order['status_kz'];         

                            OrderDeliveryInfo::where([['order_id', $lead_id],['d_type', 'ketkz']])->update($dataInfo);                        
                        }
                        
                        $leadInfo['status_group_3'] = $statusKetToCrm[$order['send_status']]['status_group_3'];             
                        
                        if(!empty($leadInfo['status_group_3'])){                
                            if($checkArray[$lead_id]['status'] != $leadInfo['status_group_3']){
                                echo "<pre style='background:green;'>";
                                echo $lead_id.' '.$checkArray[$lead_id]['status'].' '.$leadInfo['status_group_3'].'<br>';                            
                                echo "Обновился-".$updateIter."<br>";
                                echo "</pre>";

                                $order = $this->ordersRepository->find($lead_id);
                                $order['is_unload'] = 0;
                                $order->save();

                                if(!empty($checkArray[$lead_id]['status'])) DB::table('order_status')->where(['status_type'=>3,'order_id'=>$order->id])->delete();

                                $order->statuses()->attach($leadInfo['status_group_3'],[
                                    'user_id'=> 1,
                                    'status_type'=> 3,
                                    'created_at'=>Carbon::now()->format('Y-m-d H:i:s')
                                ]);   

                                History::create([
                                    'reference_table' => $this->ordersRepository->model(),
                                    'reference_id'    => $lead_id,
                                    'actor_id'        => 1,
                                    'body'            => json_encode(['statuses' => [3=>$leadInfo['status_group_3']]], JSON_UNESCAPED_UNICODE)
                                ]);                            
                                $updateIter++;                                   
                                $new_collection[] = $order;                       

                            }
                        }
                    }
                }   
                
                $iter++;                
            } while( $iter < count($ketOrders));
            if(!empty($new_collection))$this->ordersRepository->reindexByData(collect($new_collection));  
        }
        
    }

    public function re_construct($ketOrders){
        $ketOrdersArr = [];
        foreach ($ketOrders as $ket_order) {
            if(!isset($ketOrdersArr[$ket_order->id]))$ketOrdersArr[$ket_order->id] = (array)$ket_order;                        
            if(!isset($ketOrdersArr[$ket_order->id]['cost_main'])){
                $ketOrdersArr[$ket_order->id]['cost_main'] = $ket_order->delivery_types_price;
                if(isset($ket_order->surplus_percent_price))$ketOrdersArr[$ket_order->id]['cost_main'] += $ket_order->surplus_percent_price;
            }
            $ketOrdersArr[$ket_order->id]['cost_main'] += $ket_order->quantity * $ket_order->price;            
            if(!isset($ketOrdersArr[$ket_order->id]['sales']))$ketOrdersArr[$ket_order->id]['sales'] = [];            
            if(!isset($ketOrdersArr[$ket_order->id]['sales'][$ket_order->product_id]))$ketOrdersArr[$ket_order->id]['sales'][$ket_order->product_id] = [];            
            if(!isset($ketOrdersArr[$ket_order->id]['sales'][$ket_order->product_id]['quantity']))$ketOrdersArr[$ket_order->id]['sales'][$ket_order->product_id]['quantity'] = 0;            
            $ketOrdersArr[$ket_order->id]['sales'][$ket_order->product_id]['quantity'] += $ket_order->quantity;
            $ketOrdersArr[$ket_order->id]['sales'][$ket_order->product_id]['name'] = $ket_order->sale_name;            
            $ketOrdersArr[$ket_order->id]['sales'][$ket_order->product_id]['en_name'] = str_replace(array("`","-"), array("","_"), self::translit($ket_order->sale_name));            
        }
        return $ketOrdersArr;
    }

    public function get_crm_kz_delivery(){
        $delivertCrmToKetKz = $this->get_kz_delivery_arr();                         
        return array_keys($delivertCrmToKetKz);        
    }

    public function get_crm_kg_delivery(){
        $delivertCrmToKetKz = $this->get_kg_delivery_arr();                         
        return array_keys($delivertCrmToKetKz);        
    }


    public function get_delivery($delivery_type){       
        $delivertCrmKzToKetKz = $this->get_kz_delivery_arr();                   
        if(isset($delivertCrmKzToKetKz[$delivery_type]))return $delivertCrmKzToKetKz[$delivery_type];
        $delivertCrmKgToKetKz = $this->get_kg_delivery_arr();
        if(isset($delivertCrmKgToKetKz[$delivery_type]))return $delivertCrmKgToKetKz[$delivery_type];   
    }

    static function translit($str, $is_link = false){
        
        if($is_link==true)
        {
            $sim = array (" " =>"-","?"=>"-","й" => "i", "ц" => "ts", "у" => "u", "к" => "k", "е" => "e", "н" => "n", "г" => "g", "ш" => "sh", "щ" => "sh", "з" => "z", "х" => "h", "ъ" => "'", "ф" => "f", "ы" => "y", "в" => "v", "а" => "a", "п" => "p", "р" => "r", "о" => "o", "л" => "l", "д" => "d", "ж" => "dj", "э" => "e", "я" => "ya", "ч" => "ch", "с" => "s", "м" => "m", "и" => "i", "т" => "t", "ь" => "'", "б" => "b", "ю" => "yu", "Й" => "I", "Ц" => "Ts", "У" => "U", "К" => "K", "Е" => "E", "Н" => "N", "Г" => "G", "Ш" => "Sh", "Щ" => "Sh", "З" => "Z", "Х" => "H", "Ъ" => "'", "Ф" => "F", "Ы" => "I", "В" => "V", "А" => "A", "П" => "P", "Р" => "R", "О" => "O", "Л" => "L", "Д" => "D", "Ж" => "Dj", "Э" => "E", "Я" => "Ya", "Ч" => "Ch", "С" => "S", "М" => "M", "И" => "I", "Т" => "T", "Ь" => "", "Б" => "B", "Ю" => "Yu","`" => "'","ё" => "yo","Ё" => "Yo","‎" => ""); 
        }else{
            $sim = array (" " =>" ","?"=>"-","й" => "i", "ц" => "ts", "у" => "u", "к" => "k", "е" => "e", "н" => "n", "г" => "g", "ш" => "sh", "щ" => "sh", "з" => "z", "х" => "h", "ъ" => "'", "ф" => "f", "ы" => "y", "в" => "v", "а" => "a", "п" => "p", "р" => "r", "о" => "o", "л" => "l", "д" => "d", "ж" => "dj", "э" => "e", "я" => "ya", "ч" => "ch", "с" => "s", "м" => "m", "и" => "i", "т" => "t", "ь" => "'", "б" => "b", "ю" => "yu", "Й" => "I", "Ц" => "Ts", "У" => "U", "К" => "K", "Е" => "E", "Н" => "N", "Г" => "G", "Ш" => "Sh", "Щ" => "Sh", "З" => "Z", "Х" => "H", "Ъ" => "'", "Ф" => "F", "Ы" => "I", "В" => "V", "А" => "A", "П" => "P", "Р" => "R", "О" => "O", "Л" => "L", "Д" => "D", "Ж" => "Dj", "Э" => "E", "Я" => "Ya", "Ч" => "Ch", "С" => "S", "М" => "M", "И" => "I", "Т" => "T", "Ь" => "", "Б" => "B", "Ю" => "Yu","`" => "'","ё" => "yo","Ё" => "Yo","‎" => ""); 
        }
        
        return strtr($str, $sim); 
    }

    public function get_kz_delivery_arr(){        
        $deliveryKzKetKz = array(            
            '72' => 2, //AKTAU     
            '73' => 3, //AKTOBE
            '74' => 4, //ALMATA                                    
            '75' => 5, //ASTANA
            '76' => 6, //ATYRAU
            '78' => 8, //Beineu
            '113' => 9, //EKIBASTUZ
            '85' => 10, //KARAGANDA
            '88' => 11, //KOKSHETAU
            '89' => 12, //KOSTANAI
            '90' => 13, //KYLSARY          
            '91' => 14, //KYZYLORDA            
            '93' => 15, //PAVLODAR
            '94' => 16, //PETROPAVLOVSK
            '96' => 18, //RUDNYI
            '97' => 19, //Saryagash
            '98' => 20, //SATPAEV
            '99' => 21, //SEMEI            
            '112' => 22, //SHIMKENT
            '101' => 23, //TALDYKORGAN                      
            '102' => 24, //TARAZ
            '103' => 25, //TEMIRTAU
            '104' => 26, //TURKESTAN
            '106' => 27, //URALSK
            '107' => 28, //UST-KAMENOGORSK          
            '79' => 29, //ZHANAOZEN
            '82' => 30, //Zhetysai         
            '81' => 31, //ZHEZKAZGAN   
            '84' => 56, //KAPSHAGAI            
            '108' => 86, //Hromtau
            '83' => 87, //Kandagash            
            '86' => 88, //Kaskelen         
            '105' => 89, //Uzynagash
            '100' => 90, //Talgar
            '77' => 91, //Balkhash
            '87' => 93, //Kentau
            '110' => 94, //Shieli
            '80' => 95, //Zharkent
            '92' => 97, //Merke            
            '109' => 142, //Shamalgan                                    
            '111' => 146, //Shu  
            '117' => 198, //Боровое
            '118' => 199, //Щучинск
            '119' => 200, //KORDAI
            '120' => 201, //Atbasar
            '121' => 202, //Shaxtinsk
            '122' => 203, //Saran
            '123' => 204, //Mangishlak
            '124' => 205, //Aksu
            '125' => 206, //Stepnogorsk
            '126' => 207, //RIDDER
            '127' => 208, //Zaisan
            '128' => 209, //Aksukent
            '129' => 210, //Arys
            '130' => 211, //Arkalyk
            '131' => 214, //Ushtobe
            '132' => 215, //Tenge
            '133' => 216, //Atakent
            '134' => 217, //Konyrat
            '135' => 218, //Tulkibas
            '136' => 219, //Turar
            '137' => 220, //Lenger
            '138' => 221, //Abay-Saryagash
            '139' => 222, //Abay-Zhetysai
            '140' => 223, //Karabulak
            '141' => 224, //Asykata
            '142' => 225, //Shardara
            '143' => 226, //Zhanaarka
            '144' => 227, //Lisakovsk
            '145' => 228, //Zyrianovsk
            '146' => 229, //Zhanatas
            '147' => 230, //Karatau
            '148' => 231 //Shamalgan stanciya                                                  
        );
        return $deliveryKzKetKz;
    }

    public function get_kg_delivery_arr(){
        $deliveryKgKetKz = array(           
            '19' => 34, //Бишкек курьер',
            '21' => 35, //Каракол курьер',
            '23' => 36, //Ош курьер',
            '13' => 37, //Нарын курьер',
            '16' => 38, //Кызыл-Кия курьер',
            '30' => 39, //Баткен курьер',
            '31' => 40, //Талас курьер',
            '45' => 41, //Карабалта курьер',
            '27' => 42, //Токмок курьер',
            '49' => 43, //Джалал-Абад курьер',
            '24' => 44, //Узген',
            '33' => 45, //Сокулык Почта',
            '53' => 46, //Базаркоргон Почта',
            '46' => 47, //Кант Почта',
            '52' => 48, //Балыкчи курьер',
            '36' => 49, //Новопокровка Почта',
            '48' => 50, //Ивановка Почта',
            '35' => 51, //Ноокат Почта',
            '37' => 52, //Новопавловка курьер',
            '22' => 53, //Чолпон Почта',
            '50' => 54, //Бостари Почта', 
            '51' => 55, //Беловодское Почта',
            '43' => 58, //Карасу курьер',            
            '42' => 66, //Кемин Почта',
            '25' => 212, //Тюп Почта',
            '34' => 68, //Сузак Почта',
            '38' => 69, //Массы Почта',
            '41' => 70, //Кочкор ата Почта',
            '54' => 75, //Араван Почта',
            '32' => 79, //Сулюкта почта',
            '47' => 80, //Исфана курьер',
            '40' => 81, //Кочкорка почта',
            '29' => 82, //Таш-Кумыр курьер',
            '26' => 83, //Токтогул почта',
            '44' => 84, //Каракуль почта',
            '39' => 85, //Майлуу-Суу почта'
            '68' => 156, //Кадамжай почта
            '67' => 158, //Кербен Почта
            '69' => 159, //Алабука Почта            
            '70' => 213, //Ат-Баши Почта 
        );
        return $deliveryKgKetKz;
    }
}
