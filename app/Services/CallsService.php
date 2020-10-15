<?php
namespace App\Services;

use App\Models\Order;
use App\Repositories\CallsRepository;
use App\Models\User;
use App\Models\ProjectPagePhone;
use App\Models\ProjectPage;
use App\Models\SipCallerId;
use App\Models\CallStatus;
use App\Models\AtsUser;
use App\Models\Ats;
use App\Services\OrdersService;
use App\Services\AtsUserService;
use App\Services\organizationsService;
use Config;
use Exception;
use Carbon\Carbon;
use App\Queries\PermissionQuery;
use App\Libraries\ExportToExcel;
use App\Repositories\OrdersRepository;
use App\Repositories\UsersRepository;
use App\Repositories\SipRepository;
use App\Repositories\ProjectPagePhoneRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
 
class CallsService extends Service
{
    protected $exportToExcel;
    protected $permissionQuery;
    protected $ordersRepository;
    protected $usersRepository;
    protected $ordersService;
    protected $organizationsService;
    protected $projectPagePhoneRepository;
    protected $sipRepository;    
    protected $geoService;
    protected $atsUserService;

    public function __construct(
        OrdersRepository $ordersRepository,
        UsersRepository $usersRepository,
        SipRepository $sipRepository,
		CallsRepository $CallsRepository,
        ProjectPagePhoneRepository $projectPagePhoneRepository,
        PermissionQuery $permissionQuery,
        ExportToExcel $exportToExcel,
        OrdersService $ordersService,
        OrganizationsService $organizationsService,
        GeoService $geoService,
        AtsUserService $atsUserService
		)
    {
        $this->sipRepository = $sipRepository;
        $this->ordersRepository = $ordersRepository;
        $this->usersRepository = $usersRepository;
		$this->callsRepository = $CallsRepository;
        $this->projectPagePhoneRepository = $projectPagePhoneRepository;
        $this->permissionQuery = $permissionQuery;
        $this->exportToExcel = $exportToExcel;        
        $this->ordersService = $ordersService;        
        $this->organizationsService = $organizationsService;        
		$this->geoService = $geoService;
		$this->atsUserService = $atsUserService;
    }


    public function searchByOrderIdTest($id)
    {   
    
		$order_calls = $this->callsRepository->search(
			1,
			20,
			null,
			null,
			['order_id'=>['terms'=>$id]]
		); 

		//dd($order_calls);

		
        //dd($test);             		

		/*foreach ($order_calls as $key => $call) {
			$order_calls[$key]['link'] = $this->getLink($call);
		}*/
            
		return $order_calls;

    }   

    public function searchByOrderId($id)
    {
    
		$order_calls =  $this->callsRepository->searchByParams(['match' => [
            		'order_id' => $id
            	]
            ], 
            ['id'=>'asc'])->load('call_statuses'); 

		

		$order_calls->map(function($item) {
			$item['link'] = $this->getLink($item);     			
		});          
		//dd($order_calls);
		
		/*$test = [];        

        $test = $order_calls->map(function($item) {          
           	$item['link'] = $this->getLink($item);     
			

			$temp_s = $item->getRelationValue('call_statuses'); 		

			/*$temp_s->map(function($item_s){
				dd($item_s);
			});*/


			/*$item['call_statuses'] = $temp_s;
                            
            return $item;  
        });*/

        //dd($test);             		

		/*foreach ($order_calls as $key => $call) {
			$order_calls[$key]['link'] = $this->getLink($call);
		}*/
            
		return $order_calls;

    }   

    public function getLink($data){
    	
    	$year = date('Y',strtotime($data['time']));
    	$month = date('m',strtotime($data['time']));
    	$day = date('d',strtotime($data['time']));

    	$dir_crm_records = Config::get('constants.path.dir_crm_records');
        $domain_crm_audio_records = Config::get('constants.path.domain_crm_audio_records');

    	$record_dir = $dir_crm_records.$year.'/'.$month.'/'.$day.'/'.$data['record_link'].'.mp3';
    	$record_url = $domain_crm_audio_records.$data['record_link'].'.mp3';

    	if(file_exists($record_dir))
		{
			$link = $record_dir;
		}else if(file_exists($record_url)){
			$link = $dir_crm_records.$year.$data['record_link'].'.mp3';
		}else{
			$link = 'http://dc.7282crmka.ru/rec/mp3/'.$data['record_link'].'.mp3';
		}
		
		return $link;
		 
    }

   public function doCall($data, $sip){
    	$order = $this->ordersRepository->findBy('key', $data['order_key']);
    	$phoneIndex = $data['phone_num']-1;
    	$phone = null;
    	if(isset($order['phones'][$phoneIndex]))
    		$phone = $order['phones'][$phoneIndex];
    	
    	$response = null;

    	if($phone)
    	{
    		$endpoint = "http://crmka.pro/api/v1/checker";
			$client = new Client();
			try{
				$response = $client->request('GET', $endpoint, ['query' => [
				    'act' => 'go_on_call', 
				    'id_order' => '1', 
				    'id_organization' => '67', 
				    'api_key' => 'b4d12038c9ef436b67603d0804981584', 
				    'phone' => $phone,
				    'sip' => $sip
				]]);	
			}catch(RequestException $e){
				return "Сервер коллцентра не доступен!";
			}
			
    	};

    	if($response != null)
			return json_decode($response->getBody(), true);    	

		return $response;
    }

    public function getInfoByType($data = []){    
        $start = microtime(true);	                
        $organization_id = null;
        $user_id = null;
        $order_id = null;
    	if(!isset($data['type']))return [];        
        if($data['type']=='auto' || $data['type']=='out'){            
    		
            //Если есть ID заказа
            if(isset($data['id_order']) && !empty($data['id_order'])){    			
    			$order_info = $this->ordersRepository->find($data['id_order']);    			
                if(!empty($order_info)){
                    $order_id = $data['id_order'];
                    $organization_id = $this->ordersRepository->find($data['id_order'])->organization_id;                
                }
    		}
            
            //Если есть sip соотвествующего формата
            if(isset($data['sip']) && !empty($data['sip'])){                 
    			$user_info = AtsUserService::getUserByCallerId($data['sip']);                
                if($user_info){
                    if(empty($organization_id))$organization_id = $this->organizationsService->getMyCompany($user_info->organization_id)->id;
                    $user_id = $user_info->id;
                }    	
    		}

    	}elseif($data['type']=='in'){    	
            //Если есть заказ с таким номером телефона
            $order = $this->ordersRepository->searchByParams(['match' => [
                       'phones' => $data['phone']
                   ]
               ],
               ['id'=>'asc'])->toArray();            

    		if(!empty($order)){
                $organization_id = $order[0]['organization_id'];
                $order_id = $order[0]['id'];                
            }else{                
                $orderData = [];

                //Если есть сайт с таким номером телефона
                $pages = $this->projectPagePhoneRepository->with(['pages'])->findAllBy('phone', $data['phone_tor']);     
                
                if(!is_null($pages) && $pages->count() > 0){                     
                    $pages_arr = [];
                    $pages_arr = $pages->map(function($item){
                        return [
                            'page_id' => $item->pages->id,
                            'project_id' => $item->pages->project_id
                        ];
                    });
                    $orderData['project_info'] = $pages_arr;                                    
                    $orderData['organization_id'] = $pages->first()->pages->organization_id;  
                    $organization_id = $orderData['organization_id'];                    
                }elseif(isset($data['phone_tor'])){

                    //Если нет сайта, но есть транк
                    $sip_info = [];
                    $sipCallerId = SipCallerId::where('caller_id', $data['phone_tor'])->first();                                        
                    if($sipCallerId)$sip_info = $this->sipRepository->searchById($sipCallerId->sip_id)->toArray();    
                    if(!empty($sip_info) && isset($sip_info['ats_group']['organizations'])){                                                
                        $orderData['organization_id'] = $sip_info['ats_group']['organizations'][0]['id'];  
                        $organization_id = $orderData['organization_id'];
                    }    
                }

                $orderData['statuses'] = [];                   
                $orderData['type'] = 'phone';                
                if(isset($data['phone']))$orderData['phones'] = [$data['phone']];                
                
                //Если есть номер телефона и удалось определить организацию - создаем заказ
                if(isset($orderData['phones'][0]) && isset($orderData['organization_id'])){
                    $order_data = $this->ordersService->create_v2($orderData);
                    if($order_data)$order_id = $order_data->id;
                }        		
            }
    	}       

        $insert = [                
            'id' => trim($data['call_id']),
            'queue_id' => (isset($data['queue_id']))?$data['queue_id']:null,
            'rule_id' => (isset($data['rule_id']))?$data['rule_id']:null,
            'step_id' => 0,
            'ats_group_id' => (isset($data['ats_group_id']))?$data['ats_group_id']:null,
            'phone' => $data['phone'],
            'organization_id' => $organization_id,
            'call_type' => $data['type'],                                
            'sip' => ($user_id)?$data['sip']:null,
            'order_id' => $order_id,
            'record_link' => htmlspecialchars(trim($data['call_id'])),
            'record_time' => 0,
            'time' => (isset($data['time']))?$data['time']:Carbon::now()->format('Y-m-d H:i:s'),
            'dst' => $data['phone_tor'],
            'user_id' => $user_id,
        ];   
        $minus = microtime(true) - $start;
        $this->saveLog(['call'=>$data,'speed'=>$minus]);
        return $this->create($insert);        
    }

    public function addCallStatus($calls = []){        
        $start = microtime(true);        
        $result = [];
        $new_collection = [];
        foreach ($calls as $data) {                       
            $call = null;
            if(isset($data['uniqueid']))$call = $this->callsRepository->find($data['uniqueid']); 
            if(!$call){                                
                continue;
            }
            $user_id = null;
            if(isset($data['sip']) && !empty($data['sip'])){                 
                $user_data = AtsUserService::getUserByCallerId($data['sip']);
                if($user_data)$user_id = $user_data->id;                               
            }            
            if(isset($data['disposition']))$call->disposition = strtolower($data['disposition']);
            if(isset($data['billing_time']))$call->billing_time = $data['billing_time'];
            if(isset($data['duration_time']))$call->duration_time = $data['duration_time'];
            if($user_id)$call->sip = $data['sip'];            
            if(isset($data['queue_id']))$call->queue_id = $data['queue_id'];            
            if(isset($data['weight']))$call->weight = $data['weight'];       
            if(isset($data['step_id']))$call->step_id = $data['step_id'];       
            if(isset($data['order_id']))$call->order_id = $data['order_id'];       
            if(isset($data['call_type']))$call->call_type = $data['call_type'];       
            if(!empty($user_id))$call->user_id = $user_id;
            if(isset($data['dcontext'])){
                if(strstr($data['dcontext'],'auto_reverse_')){                       
                    $call->call_type = 'auto_reverse';
                }
            }

            $reindex = false;
            
            if(isset($data['queue_history'])){

                if(!is_array($data['queue_history']))$data['queue_history'] = json_decode($data['queue_history'], true);               

                if(count($data['queue_history']) !== 0 ){                                
                    foreach ($data['queue_history'] as $history)
                    {   
                        if(!isset($history['event'])) continue;
                        if(empty($history['event'])) continue;
                        
                        $temp_user_id = null;

                        if(isset($history['agent']) && !empty($history['agent'])) {     
                            $temp_user_data = AtsUserService::getUserByCallerId($history['agent']);
                            if($temp_user_data)$temp_user_id = $temp_user_data->id;                   
                        }

                        $call_status = [];
                        if(isset($history['event']))$call_status['status'] = trim($history['event']);
                        if($temp_user_id)$call_status['agent'] = trim($history['agent']);
                        if(isset($history['add_time']))$call_status['time'] = trim($history['add_time']);
                        if($temp_user_id)$call_status['user_id'] = $temp_user_id;
                        $call_status['call_id'] = trim($data['uniqueid']);                    
                        $call_record = CallStatus::firstOrNew($call_status);
                        if(!isset($call_record->id)){
                            $call_record->save();
                            $reindex = true;
                        }                                                
                    }
                }else{
                    if(isset($data['disposition'])){
                        if(strtolower($data['disposition'])=='answered'){                
                            $reindex = $this->saveCallStatusConnect($data, $user_id);                    
                        }    
                    }
                }           
            }else{
                if(isset($data['disposition'])){
                    if(strtolower($data['disposition'])=='answered'){                
                        $reindex = $this->saveCallStatusConnect($data, $user_id);                    
                    }
                }
            }

            if($call->save()){                                
                if(!empty($call->getChanges()) || $reindex){
                    $result[$data['uniqueid']] = 'update';
                    $new_collection[] = $call;
                }
            }else{
                $this->saveLog($data);
            }
            //$this->callsRepository->reindexModel($call, true);
        }
        $this->callsRepository->reindexByData(collect($new_collection));  
        $minus = microtime(true) - $start;
        
        $this->saveLog(['call_status'=>$calls,'speed'=>$minus]);            
        return $result;
    }     

    public function saveCallStatusConnect($data, $user_id){
        $call_status = [];
        $call_status['status'] = 'connect';
        if($user_id)$call_status['agent'] = $data['sip'];                    
        if($user_id)$call_status['user_id'] = $user_id;
        $call_status['call_id'] = $data['uniqueid'];

        $call_status_result = CallStatus::firstOrNew($call_status);

        if(!isset($call_status_result->id)){
            $call_status_result->time = Carbon::now()->format('Y-m-d H:i:s');
            $call_status_result->save();                        
            return true;
        }else{
            return false;
        }                    
    }

    public function saveLog($data, $id = null){
        $file_name = date('Y-m-d').'.txt';
        $dir = base_path('storage/app/files/calls_updates');
        if (!file_exists($dir) && !is_dir($dir)) {
            if(!mkdir($dir, 0755, true)) {
                return ['logs'=>"Не удалось создать каталог $dir"];
            }
        }        
        $strD = '======== '.date('Y-m-d H:i:s')." =========\n";        
        $strD .= (($id)?$id:'')."\n";        
        $strD .= (json_encode($data))."\n";              
        file_put_contents("$dir/$file_name", $strD, FILE_APPEND | LOCK_EX);
    }

    public function create($data){
        $call = $this->callsRepository->create($data);    
        if ($call) {
            $this->callsRepository->reindexModel($call, true);

            return $call;
        }
        return false;
    }
    
    public function goOperatorCall($order_id, $number)
    {
        $this->clearErrors();

        $phones = Order::find($order_id)->phones;
        $phone = $phones[$number-1];

        $operator = $this->atsUserService->isOnline();
        if (!$operator) {
            $this->pushError(["No active operator", 422, ["Нет активного сип аккаунта"]]);
            return false;
        }

        $ats_user = AtsUser::where('id', $operator->id)->with('atsGroup')->first();
        $ats = Ats::find($ats_user->atsGroup->ats_id);
        $url = "http://$ats->ip/aster_api/APIAsterisk.php?act=go_operator_call&phone=$phone&sip=$operator->login&id_order=$order_id&id_organization=$ats_user->ats_group_id&api_key=$ats->key";

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    protected function getSearchRepository()
    {
        return $this->callsRepository;
    }

    protected function addSearchConditions(User $user=null,array $filters=null)
    {
        return $filters;
	}
	
	protected function getPermissionQuery(){
        return $this->permissionQuery;
	}
	
	protected function getExportToExcelLib(){
        return $this->exportToExcel;
    }

}
