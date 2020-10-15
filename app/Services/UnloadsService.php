<?php
namespace App\Services;

use App\Repositories\UnloadsRepository;
use App\Repositories\OrdersRepository;
use App\Repositories\OrganizationsRepository;
use DB;
use Exception;

use App\Queries\PermissionQuery;
use App\Libraries\ExportToExcel;
use App\Models\User;
use App\Models\ProjectPage;
use App\Models\Project;
use App\Models\Comment;
use App\Models\Sale;
use App\Models\OrderStatus;
use App\Models\AtsQueue;
use App\Models\Order;

use App\Services\OrdersService;

use Carbon\Carbon;


class UnloadsService extends Service
{
    protected $unloadsRepository;
    protected $organizationsRepository;
    protected $permissionQuery;
    protected $exportToExcel;
    protected $ordersRepository;
    protected $ordersService;

    public function __construct(
        UnloadsRepository $unloadsRepository,
        OrganizationsRepository $organizationsRepository,
        PermissionQuery $permissionQuery,
        ExportToExcel $exportToExcel,
        OrdersRepository $ordersRepository,
        OrdersService $ordersService)
    {
        $this->unloadsRepository = $unloadsRepository;
        $this->organizationsRepository = $organizationsRepository;
        $this->permissionQuery = $permissionQuery;
        $this->exportToExcel = $exportToExcel;
        $this->ordersRepository=$ordersRepository;
        $this->ordersService=$ordersService;
    }

   /**
     * Create unload
     *
     * @param array $unloadData
     * @throws \Throwable
     * @return object - unload
     */
 /*     public function create(array $unloadData)
    {
        $organization = $this->organizationsRepository->find($unloadData['organization_id']);

        if (!$organization) {
            throw new Exception('Нет такой организации');
        }

        $unload = DB::transaction(function () use ($unloadData) {
            return $this->unloadsRepository->create($unloadData);
        });

        if ($unload) {
            $this->unloadsRepository->reindexModel($unload, true);
        }

        return $unload;
    } */


    public function update($id, $data, $reindex = false)
    {
        $unload = DB::transaction(function () use ($data, $id) {
            if($id==0){
                $date = date('Y-m-d H:i:s.u');
                $data['api_key'] = md5($date);
                return $this->unloadsRepository->create($data);
            }else{
                return $this->unloadsRepository->update($data, $id);
            }
        });

        if($reindex) {
           $reindexResponse = $this->unloadsRepository->reindexModel($unload, true);
        }

        return $unload;
    }

    public function getById($id){
        $filter = ["id","=",$id];
        $request['skip'] = 0;
        $request['take'] = 10;
        $request['filter'] = json_encode($filter);
        $upload = $this->unloadsRepository->dxSearch($request);
        return $upload;
    }

    public function getOrdersByAPIKey($key, $skip=0, $take=10, $add_filter=[], $add_sort=[], $add_list=[], $add_conditions=[]){
        $filter = ["api_key","=",$key];
        $request['skip'] = 0;
        $request['take'] = 1;
        $request['filter'] = json_encode($filter);

        $upload = $this->unloadsRepository->dxSearch($request);

        if(count($upload->toArray())==0)
            return false;

        if(!$upload->toArray()[0]['is_work'])
            return false;

        $config = json_decode($upload->toArray()[0]['config']);
        
        $table = $config->table;

        $list = [];

        foreach($table as $key=>$t){
            if($t->visible){
                $key_with_dot = explode(".", $key);
                if(count($key_with_dot)>1){

                    $key = $key_with_dot[0];

                    if($key_with_dot[1]=='created_at')
                        $key = null;
                }
                if($key!=null)
                    $list[] = $key;
            }
        }

        if(!empty($add_list)){
            foreach ($add_list as $add_field) {
                $list[] = $add_field;        
            }
        }              

        $request['source']=json_encode($list);

        if(!empty($add_filter)){
            if(!empty($config->filter)){                
                if (!in_array("and", $config->filter)) {                   
                    $temp_filter = $config->filter;
                    $config->filter = [];
                    $config->filter = [$temp_filter];
                }              
                $config->filter[] = "and";
            }            
            $config->filter[] = $add_filter;
        }                   

        if(isset($config->filter)){
            $request['filter']=json_encode($config->filter);
        }else{
            $request['filter']=null;
        }

        if(!empty($add_sort)){
            $config->sort = $add_sort;
        }

        if(isset($config->sort)){
            $request['sort']=json_encode($config->sort);
        }

        $request['skip'] = $skip;
        $request['take'] = $take;        

        $orders = $this->ordersRepository->dxSearch($request)->toArray();
        foreach($orders as $okey=>$o){
            // dd($orders[$okey]);
            foreach($o as $key=>$value){

                if(!isset($orders[$okey]['country_code']) && empty($orders[$okey]['country_code']))$orders[$okey]['country_code'] = "";
                if(!isset($orders[$okey]['phone_country']) && empty($orders[$okey]['phone_country']))$orders[$okey]['phone_country'] = "";

                if($key=="geo"){
                    $orders[$okey]['country_code'] = $value['code'];
                    $orders[$okey]['phone_country'] = $value['name_ru'];
                    unset($orders[$okey]['geo']);
                }

                if($key=="city"){
                    $orders[$okey]['sity'] = $value;
                    unset($orders[$okey]['city']);
                }

                if($key=="responsible_id"){
                    $orders[$okey]['id_responsible'] = $value;
                    unset($orders[$okey]['responsible_id']);
                }

                if($key=="gasket_id"){
                    $orders[$okey]['id_gasket'] = $value;
                    unset($orders[$okey]['gasket_id']);
                }


                if($key=="webmaster_id"){
                    //  $orders[$okey]['id_webmaster'] = $value;
                     unset($orders[$okey]['webmaster_id']);
                }


                if($key=="import_webmaster_id"){
                    $orders[$okey]['id_webmaster'] = $value;
                    unset($orders[$okey]['import_webmaster_id']);
                }

                if($key=="transit_webmaster_id"){
                    $orders[$okey]['id_webmaster_transit'] = $value;
                    unset($orders[$okey]['transit_webmaster_id']);
                }

                if($key=="flow_id"){
                    $orders[$okey]['id_flow'] = $value;
                    unset($orders[$okey]['flow_id']);
                }

                if($key=="client_email"){
                    $orders[$okey]['mail_client'] = $value;
                    unset($orders[$okey]['client_email']);
                }
                
                if($key=="info"){
                    $orders[$okey]['dop_info'] = $value;
                    unset($orders[$okey]['info']);
                }

                if($key=="second_id"){
                    $orders[$okey]['id_second'] = $value;
                    unset($orders[$okey]['second_id']);
                }

                if($key=="source_id"){
                    $orders[$okey]['id_source'] = $value;
                    unset($orders[$okey]['source_id']);
                }

                if($key=="sex_id"){
                    $orders[$okey]['id_sex'] = $value;
                    unset($orders[$okey]['sex_id']);
                }

                if($key=="device_id"){
                    $orders[$okey]['id_device'] = $value;
                    unset($orders[$okey]['device_id']);
                }

                if($key=="created_at"){
                    $orders[$okey]['create_date'] = $value;
                    unset($orders[$okey]['created_at']);
                }

                if($key=="ordered_at"){
                    $orders[$okey]['order_date'] = $value;
                    unset($orders[$okey]['ordered_at']);
                }

                if($key=="age_id"){
                    $orders[$okey]['id_age'] = $value;
                    unset($orders[$okey]['age_id']);
                }

                if($key=="organizations"){
                    $orders[$okey]['id_organization'] = $value['id'];
                    unset($orders[$okey]['organizations']);
                }

                if($key=="delivery_date_finish"){
                    $orders[$okey]['delivery_time']= $value;
                    unset($orders[$okey]['delivery_date_finish']);
                }

                if($key=="delivery_types_price"){
                    $orders[$okey]['delivery_price']= $value;
                    unset($orders[$okey]['delivery_types_price']);
                }

                if($key=="delivery_type"){
                    $orders[$okey]['delivery_type']['id'] = $value['id'];
                    $orders[$okey]['delivery_type']['name'] = $value['title'];
                    unset($orders[$okey]['delivery_type']['title']);
                }

                if($key=="operator"){
                    $orders[$okey]['manager']['name'] = $value['title'];
                    $orders[$okey]['manager']['id_caller'] = $value['id'];

                    unset($orders[$okey]['operator']);
                }

                if($key=="project_page" && isset($value[0])){
                    $orders[$okey]['site']= $value[0];
                    $orders[$okey]['site']['url']= $value[0]['link'];
                    unset($orders[$okey]['project_page']);
                }

                if($key=="projects"){
                    $items = [];
                      foreach($value as $v){
                        $item =  [
                            'id'                => $v['id'],
                            'name'             => $v['title'],
                            'import_id'         => $v['import_id'],
                            'name_en'           => $v['name_en'],
                            'desc'              => $v['description'],
                            'sms_sender'        => $v['sms_sender'],
                            'countries'         => $v['countries'],
                            'hold'              => $v['hold'],
                            'sex'               => $v['sex'],
                            'kc_category'       => $v['kc_category'],
                            'name_for_client'   => $v['name_for_client']
                        ];
                        $items[] = $item;
                    }
                    $orders[$okey]['project']= $items;
                    if(count($items)==0){
                        $item = [];
                        $item['id'] = null;
                        $item['name'] = null;
                        $items[]=$item;
                        $orders[$okey]['project']= $items;
                    }
                    unset($orders[$okey]['projects']);
                }

                if($key=="dial_steps"){
                    if(isset($add_conditions['queue_id'])){
                        if(count($value) > 0){
                            foreach($value as $v){
                                if($v['queue_id']==$add_conditions['queue_id']){
                                    $orders[$okey]['dial_step']= $v['dial_step'];
                                    $orders[$okey]['dial_time']= $v['dial_time'];
                                    break;
                                }else{
                                    $orders[$okey]['dial_step']= 0;
                                    $orders[$okey]['dial_time']= strtotime('now');        
                                }                        
                            }    
                        }else{
                            $orders[$okey]['dial_step']= 0;
                            $orders[$okey]['dial_time']= strtotime('now');
                        }
                        unset($orders[$okey]['dial_steps']);
                    }                                      
                }

                $array_by_underscore = explode("_", $key);
                if( count($array_by_underscore) ==2 && $array_by_underscore[0]=='status'){
                    $orders[$okey]['status_group_'.$array_by_underscore[1]]['id']= $value['id'];
                    $orders[$okey]['status_group_'.$array_by_underscore[1]]['name']= $value['name'];
                    $orders[$okey]['status_group_'.$array_by_underscore[1]]['autor']= $value['autor'];
                    $orders[$okey]['status_group_'.$array_by_underscore[1]]['add_date']= $value['created_at'];
                    unset($orders[$okey][$key]);
                }

                if($key=="comments" && count($value)>0){
                    unset($orders[$okey]['comments']);
                    $orders[$okey]['comments']['comment']= $value;
                }

                if($key=="phones"){
                    $orders[$okey]['phone']= $value[0];
                    if(!isset($value[1]))
                        $value=null;
                    if(!isset($value[2]))
                        $value=null;
                    $orders[$okey]['phone_2']= $value[1];
                    $orders[$okey]['phone_3']= $value[2];
                    unset($orders[$okey]['phones']);
                }

                if($key=="upsale1"){
                    $orders[$okey]['sales_additional_count'] = $value;
                    unset($orders[$okey]['upsale']);
                }

                if($key=="upsale2"){
                    $orders[$okey]['sales_additional_lvl_2_count'] = $value;
                    unset($orders[$okey]['upsale']);
                }

                if($key=="sales"){
                    $sale_array = [];
                    foreach($value as $v){
                        $item =  [
                            'id'                   => $v['id'],
                            'product_id'           => $v['product_id'],
                            'autor_additional'     => $v['upsale_user_id'],
                            'id_lead'              => $v['lead_id'],
                            'quantity'             => $v['quantity'],
                            'quantity_price'       => $v['quantity_price'],
                            'quantity_pay'         => $v['quantity_pay'],
                            'weight'               => $v['weight'],
                            'cost_price'           => isset($v['price']) ? $v['price'] : $v['cost_price'],
                            'comment'              => $v['comment'],
                            'name'                 => $v['name'],
                            'article'              => $v['article']
                        ];

                        $item['upsale_1'] = 0;
                        $item['upsale_2'] = 0;

                        if($v['upsale']==1){
                            $item['upsale_1'] = 1;
                        }
                        if($v['upsale']==2){
                            $item['upsale_1'] = 1;
                            $item['upsale_2'] = 1;
                        }

                        $sale_array[]=$item;
                    }

                    if(count($sale_array)>0){
                        unset($orders[$okey]['sales']);
                        $orders[$okey]['sales']['sale']=$sale_array;
                    }
                }


                unset($orders[$okey]['statuses']);
            }
            if(!isset($orders[$okey]['site'])){
                $orders[$okey]['site'] = [];
                $orders[$okey]['site']['id'] = null;
                $orders[$okey]['site']['url'] = null;
            }

            if(!isset($orders[$okey]['dop_info'])) $orders[$okey]['dop_info'] = null;
            if(!isset($orders[$okey]['client_name'])) $orders[$okey]['client_name'] = null;
            if(!isset($orders[$okey]['status_group_1'])) $orders[$okey]['status_group_1'] = null;
            if(!isset($orders[$okey]['status_group_2'])) $orders[$okey]['status_group_2'] = null;
            if(!isset($orders[$okey]['status_group_3'])) $orders[$okey]['status_group_3'] = null;
            if(!isset($orders[$okey]['status_group_4'])) $orders[$okey]['status_group_4'] = null;
            if(!isset($orders[$okey]['status_group_5'])) $orders[$okey]['status_group_5'] = null;
            if(!isset($orders[$okey]['status_group_6'])) $orders[$okey]['status_group_6'] = null;
            if(!isset($orders[$okey]['status_group_7'])) $orders[$okey]['status_group_7'] = null;
            if(!isset($orders[$okey]['status_group_8'])) $orders[$okey]['status_group_8'] = null;
            if(!isset($orders[$okey]['status_group_9'])) $orders[$okey]['status_group_9'] = null;
            if(!isset($orders[$okey]['status_group_10'])) $orders[$okey]['status_group_10'] = null;
            if(!isset($orders[$okey]['id_webmaster'])) $orders[$okey]['id_webmaster'] = null;
            if(!isset($orders[$okey]['id_webmaster_transit'])) $orders[$okey]['id_webmaster_transit'] = null;
        }

        return $orders;
    }

    public function setOrdersFromAPI($orders, $organization_id){

        $orderData = [];
        $order = null;
        $order_ids = [];

        foreach($orders as $o){

            if(isset($o["id"]))
                $order_ids[] = $o["id"];

            $orderData = $o;
            
            $spp_from_1c = 0.0;
            if (isset($o['surplus_percent_price'])) {
                $spp_from_1c = $o['surplus_percent_price'];
                unset($orderData['surplus_percent_price']);
            }
            
            if($organization_id)$orderData['organization_id'] = $organization_id;

            if(isset($o["sity"])){
                $orderData['city'] = $o["sity"];
                if(is_array($o["sity"]))
                    $orderData['city'] = "";
                unset($orderData['sity']);
            }

            if(isset($o['dial_time']) && isset($o['dial_step']) && isset($o['queue_id'])){
                if(AtsQueue::find($o['queue_id'])){
                    $orderData['dial_step_info'] = [
                        'dial_time' => date('Y-m-d H:i:s',$o['dial_time']),
                        'dial_step' => $o['dial_step'],
                        'queue_id' => $o['queue_id']
                    ];                
                }
            }

            if(isset($o['dial_time']))unset($orderData['dial_time']);
            if(isset($o['dial_step']))unset($orderData['dial_step']);
            if(isset($o['queue_id']))unset($orderData['queue_id']);

            if(isset($o["id_responsible"])){
                $orderData['responsible_id'] = $o["id_responsible"];
                if(is_array($orderData['responsible_id']))
                    $orderData['responsible_id'] = null;
                unset($orderData['id_responsible']);
            }

            if(isset($o["id_gasket"])){
                $orderData['gasket_id'] = $o["id_gasket"];
                if(is_array($orderData['gasket_id']))
                    $orderData['gasket_id'] = null;
                unset($orderData['id_gasket']);
            }

            if(isset($o["id_webmaster"])){
                $orderData['webmaster_id'] = $o["id_webmaster"];
                unset($orderData['id_webmaster']);
            }

            if(isset($o["id_webmaster_transit"])){
                $orderData['transit_webmaster_id'] = $o["id_webmaster_transit"];
                unset($orderData['id_webmaster_transit']);
            }

            if(isset($o["id_flow"])){
                $orderData['flow_id'] = $o["id_flow"];
                unset($orderData['id_flow']);
            }

            if(isset($o["mail_client"])){
                $orderData['client_email'] = $o["mail_client"];
                if(is_array($o["mail_client"]))
                    $orderData['client_email'] = "";

                unset($orderData['mail_client']);
            }

            if(isset($o["dop_info"])){
                $orderData['info'] = $o["dop_info"];
                unset($orderData['dop_info']);
            }

            if(isset($o["id_second"])){
                $orderData['second_id'] = $o["id_second"];
                unset($orderData['id_second']);
            }          

            if(isset($o["id_source"])){
                $orderData['source_id'] = $o["id_source"];
                unset($orderData['id_source']);
            }

            if(isset($o["id_sex"])){
                $orderData['sex_id'] = $o["id_sex"];
                unset($orderData['id_sex']);
            }

            if(isset($o["id_device"])){
                $orderData['device_id'] = $o["id_device"];
                unset($orderData['id_device']);
            }

            if(isset($o["create_date"])){
                $orderData['created_at'] = $o["create_date"];
                unset($orderData['create_date']);
            }

            if(isset($o["order_date"])){
                $orderData['ordered_at'] = $o["order_date"];
                unset($orderData['order_date']);
            }

            if(isset($o["id_age"])){
                $orderData['age_id'] = $o["id_age"];
                unset($orderData['id_age']);
            }

            if(isset($o["delivery_time"])){
                $orderData['delivery_date_finish'] = $o["delivery_time"];
                unset($orderData['delivery_time']);
            }

            if(isset($o["delivery_type"])){
                if(isset($o["delivery_type"]["id"]))
                    $orderData['delivery_types_id'] = $o["delivery_type"]["id"];
                if(is_array($orderData['delivery_types_id']))
                    $orderData['delivery_types_id'] = 0;
                $dt = DB::table('delivery_types')->where('id', (int)$orderData['delivery_types_id'])->first();
                $ord = null;
                if (isset($o["id"])) {
                    // Ищем заказ с нужным айди без реализации
                    $ord = DB::table('orders')
                        ->where([['id', $o["id"]], ["status_1c_3", "<>", "True"]])
                        ->select('surplus_percent_price')
                        ->first();
                }
                if($dt && (double)$dt->surplus_percent > 0) { // Если тип доставки с ПП
                    $update_pp = false;
                    if (isset($o["id"])) {
                        if (!is_null($ord)) { // Если заказ найден
                            $empty_pp = is_null($ord->surplus_percent_price) || (int)$ord->surplus_percent_price == 0;
                            if($empty_pp) {   // и ПП не заполнено
                                $update_pp = true; // то будем расчитывать ПП
                            }
                        }
                    } else {
                        $update_pp = true;
                    }
                    if ($update_pp && is_array($o['sales'])) { // Если надо расчитать ПП и есть товары
                        $price = 0.00;
                        foreach ($o['sales'] as $key => $sale) {
                            if (isset($sale['quantity_price'])) {
                                $price += (double)$sale['quantity_price'];
                            }
                        }
                        if (isset($o['delivery_price'])) {
                            $price += (double)$o['delivery_price'];
                        }
                        $spp = $price * (double)$dt->surplus_percent * 0.01;
                        $orderData['surplus_percent_price'] = $spp;
                        $orderData['is_unload'] = 0;
                    }
                } else { // Если тип доставки без ПП

                    if (!is_null($ord)) { // Если заказ найден
                        $empty_pp = is_null($ord->surplus_percent_price) || (int)$ord->surplus_percent_price == 0;
                        if(!$empty_pp) { // И ПП не пустое
                            // То обнуляем ПП
                            $orderData['surplus_percent_price'] = 0.0;
                            $orderData['is_unload'] = 0;
                        }
                    }
                }
                if (!isset($orderData['surplus_percent_price'])) { // Если ПП не был расчитан
                    $orderData['surplus_percent_price'] = $spp_from_1c; // То записываем то, что пришло от 1с
                }
                unset($orderData['delivery_type']);
            }

            if(isset($o["manager"])){
                $orderData['operator_id'] = $o["manager"]["id_caller"];
                if(is_array($orderData['operator_id']))
                    $orderData['operator_id'] = null;
                unset($orderData['manager']);
            }

            if(isset($o["phone"]) && !is_array($o["phone"])){
                $orderData['phones'] = [];
                $orderData['phones'][] = preg_replace('/[^\d]/', '', $o["phone"]);
            }
            if(isset($o["phone_2"]) && !is_array($o["phone_2"])){
                $orderData['phones'][] = preg_replace('/[^\d]/', '', $o["phone_2"]);
            }
            if(isset($o["phone_3"]) && !is_array($o["phone_3"])){
                $orderData['phones'][] = preg_replace('/[^\d]/', '', $o["phone_3"]);
            }

            unset($orderData['id']);
            unset($orderData['phone']);
            unset($orderData['phone_2']);
            unset($orderData['phone_3']);
            unset($orderData['id_organization']);
            unset($orderData['geo']);
            unset($orderData['delivery_type']);
            unset($orderData['site']);
            unset($orderData['project']);
            unset($orderData['sales']);

            //integers
            if(isset($o["site_order_id"]) && is_array($o["site_order_id"])){
                $orderData["site_order_id"] = 0;
            }

            if(isset($o["delivery_price"]) && is_array($o["delivery_price"])){
                $orderData["delivery_price"] = 0;
            }

            if(isset($o["real_profit"])){
                $orderData['real_profit'] = intval($o["real_profit"]);
            }

            if(isset($o["products_total"])){
                $orderData['products_total'] = intval($o["products_total"]);
            }

            if(isset($o["cost_main"])){
                $orderData['cost_main'] = intval($o["cost_main"]);
            }

            if(isset($o["id"])){
                $orderData['id'] = intval($o["id"]);
            }

            if(isset($o["profit"])){
                $orderData['profit'] = intval($o["profit"]);
            }


            //strings
            if(isset($o["housing"]) && is_array($o["housing"])){
                $orderData["housing"] = "";
            }

            if(isset($o["street"]) && is_array($o["street"])){
                $orderData["street"] = "";
            }

            if(isset($o["area"]) && is_array($o["area"])){
                $orderData["area"] = "";
            }

            if(isset($o["postcode"]) && is_array($o["postcode"])){
                $orderData["postcode"] = "";
            }

            if(isset($o["warehouse"]) && is_array($o["warehouse"])){
                $orderData["warehouse"] = "";
            }

            if(isset($o["home"]) && is_array($o["home"])){
                $orderData["home"] = "";
            }

            if(isset($o["room"]) && is_array($o["room"])){
                $orderData["room"] = "";
            }

            if(isset($o["status_1c_2"]) && is_array($o["status_1c_2"])){
                $orderData["status_1c_2"] = "";
            }

            if(isset($o["status_1c_1"]) && is_array($o["status_1c_1"])){
                $orderData["status_1c_1"] = "";
            }

            if(isset($o["status_1c_3_time"])){
                $orderData["status_1c_3_time"] = $o["status_1c_3_time"];
            }
            

            if(isset($o["track_number"]) && is_array($o["track_number"])){
                $orderData["track_number"] = "";
            }

            if(isset($o["region"]) && is_array($o["region"])){
                $orderData["region"] = "";
            }

            if(isset($o["warehouse_id"]) && is_array($o["warehouse_id"])){
                $orderData["warehouse_id"] = "";
            }            

            if(isset($o["project"]) ){
                $projects = [];
                $project = null;
                $orderData["project_info"] = [];

                $project = $o["project"];
                if(isset($o["site"]) && $o["site"] != []){    
                    $project['project_page'] = $o["site"];
                }else{
                    $project['project_page'] = [];
                }

                if(isset($o["gasket"]) && $o["gasket"] != []){    
                    $project['project_gasket'] = $o["gasket"];
                }else{
                    $project['project_gasket'] = [];
                }

                $orderData["project_info"]=$project;
                
                unset($orderData['project']);
            }elseif(isset($o["site"]) && $o["site"] != []){
                $orderData["project_info"] =  $o["site"];
            }
            

            unset($orderData['project']);
            unset($orderData['site']);


            if(isset($o["comments"])){
                
                $orderData['comment'] = [];
                foreach ($o["comments"] as $cvalue) {
                    $orderData['comment'][] = $cvalue;
                }

                //Если не массив делаем массивом
                if(isset($orderData['comment']["id"])){
                    $orderData['comment'] = [$orderData['comment']];
                }

                unset($orderData['comments']);
            }

            if(isset($o["sales"])){
                $orderData['sales'] = [];
                foreach ($o["sales"] as $svalue) {
                    $orderData['sales'][] = $svalue;
                }

                //Если не массив делаем массивом
                if(isset($orderData['sales']["id"])){
                    $orderData['sales'] = [$orderData['sales']];
                }
            }

            $orderData['statuses'] = [];
            foreach($o as $okey=>$ovalue){                
                $array_by_underscore = explode("_", $okey);
                if( $array_by_underscore[0]=='status' && $array_by_underscore[1]=='group'){           
                    if(isset($ovalue['id'])){
                        $orderData['statuses'][$array_by_underscore[2]] = intval($ovalue['id']);          
                    }else{
                        $orderData['statuses'][$array_by_underscore[2]] = intval($ovalue);          
                    }     
                }
            }


            $orderData['type'] = 'API';
            // $orderData['is_unload'] = 1;
            $orderData['manager_id'] = 1; // Пользователь 1 -  Загружен по API            

            // is_unload
            // 1 - новый заказ. Это заказы, еще не обработанные оператором.
            //0 - обновлен. Это заказы уже обработанные оператором.
            //2 - передан. Это заказы переданные в CRM, через API.

            if(!isset($o["id"]) || (isset($o["id"]) && $o["id"]==[]) || (isset($o["id"]) && $o["id"]=="")){

                $orders=[];

                if(!isset($o["id"]) && isset($o["import_id"]))
                    $orders = $this->ordersRepository->findWhere(["import_id"=>$o["import_id"]])->toArray();            

                if(count($orders)==0){
                    $order = $this->ordersService->create_v2($orderData);
                    $order["is_new"] = true;
                }else{
                    unset($orderData['manager_id']);
                    unset($orderData['type']);
                    // if(!isset($orderData['dial_step']))
                    //     $orderData['is_unload'] = 2;
                    $order = $this->ordersService->updateBy($orders[0]['id'], $orderData,"id", null, false);
                }
            }else{
                $orders = $this->ordersRepository->findWhere(["id"=>$o["id"]])->toArray();
    
                if(count($orders)==0 && isset($o["import_id"]))
                    $orders = $this->ordersRepository->findWhere(["import_id"=>$o["import_id"]])->toArray();

                if(count($orders)==0){
                    $order = $this->ordersService->create_v2($orderData);
                    $order["is_new"] = true;
                }else{
                    unset($orderData['manager_id']);
                    unset($orderData['type']);
                    // if(!isset($orderData['dial_step']))
                    //     $orderData['is_unload'] = 2;
                    $order = $this->ordersService->updateBy($orders[0]['id'], $orderData,"id", null, false);
                }
            }


        }
        
        $orders = Order::whereIn('id', $order_ids)->get();       
        $this->ordersRepository->reindexByData($orders);

        return $order;
    }

    public function webinarAPI($data, $organization_id){
        
        $phone = preg_replace('/[^\d]/', '', $data["phone"]);
        
        $order = DB::table('orders')
            ->join('order_project', 'orders.id', '=', 'order_project.order_id')
            ->where([ 
                ['order_project.project_id','=',  $data["project_id"]],
                ['orders.phones','like',  '%'.$data["phone"].'%'],
            ])
            ->orderByRaw('created_at DESC')
            ->first();
        

        if($order == null){
            $order = DB::table('orders')
            ->join('order_project', 'orders.id', '=', 'order_project.order_id')
            ->where([ 
                ['order_project.project_id','=',  $data["project_id"]],
                ['orders.client_email','like',  '%'.$data["email"].'%'],
            ])
            ->orderByRaw('created_at DESC')
            ->first();
        }

        $new_order['organization_id'] = $organization_id; 
        $new_order['statuses'][$data["status"]["type"]] = $data["status"]["value"]; 
        $new_order['comment'] = $data["comment"]; 
        $new_order['manager_id'] = 1; 

        if($order == null){
            $new_order['client_name'] = $data["name"]; 
            $new_order['phones'][] = $data["phone"]; 
            $new_order['client_email'] = $data["email"]; 
            $new_order["project_info"] = ["id"=>$data["project_id"], 'project_page'=>[]];
            $order = $this->ordersService->create_v2($new_order);
        }else{
            $order = $this->ordersService->updateBy($order->key,  $new_order, "key");
        }
    
        return $order;
    }

    protected function addSearchConditions(User $user=null,array $filters=null)
    {
        return $filters;
    }

    public function dxGroupedSearch($request){
        $result = $this->unloadsRepository->dxGroupedSearch($request);
        return $result;
    }

    protected function getSearchRepository()
    {
        return $this->unloadsRepository;
    }

    public function getPermissionQuery(){
        return $this->permissionQuery;
    }

    public function getExportToExcelLib(){
        return $this->exportToExcel;
    }
}
