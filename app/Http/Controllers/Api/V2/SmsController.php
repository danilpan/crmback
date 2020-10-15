<?php
namespace App\Http\Controllers\Api\V2;

use App\Http\Requests\Api\V2\SearchRequest;
use App\Http\Requests\Api\V2\SmsCreateFewRequest;
use App\Http\Requests\Api\V2\SmsCreateRequest;
use App\Repositories\OrdersRepository;
use App\Repositories\SmsRepository;
use App\Services\SmsService;
use App\Models\Order;

class SmsController extends Controller
{


    public function getList(SearchRequest $request, SmsService $service){
        
        return;
    }

    public function getById($key, SmsRepository $smsRepository, OrdersRepository $ordersRepository, SmsService $service)
    {
        
        $order_info = $ordersRepository->searchByParams(
            ['match' => [
                'key' => $key                ]
            ], 
            ['key'=>'asc']
        )->toArray();   

        $sms = $smsRepository->with(['user'])->findAllBy('order_id', $order_info[0]['id'])->sortBy('id');

        $permission_list = [];        
        $permission_list["menu.main.orders.view_phone_number"] =  $this->cani("menu.main.orders.view_phone_number");                    

        return $service->prepareSms($sms, $permission_list);
        
    }
 
    public function create(SmsCreateRequest $request, SmsService $service, OrdersRepository $ordersRepository)
    {

        $provider = $service->create($request->key, 1);
        $sms = $service->smsBuilder($request->sms_template_id, $request->key, $ordersRepository);
        if(!$sms){
            return $this->errorResponse('Ошибка смс', 404, ['sms_template' => ['Отсутствует переменная']]);
        }
        if(!$provider){
            return $this->errorResponse('Ошибка заказа', 404, ['sms_phone'=>['Не найден провайдер']]);
        }else{
            try {
                $result = $provider->send($request->sms_phone, $sms);
                if($result){                                        
                    return $result;
                }else{
                    return $this->errorResponse('Ошибка заказа', 404, ['sms_phone'=>['Ошибка отправки']]);    
                }
            }catch(\Throwable $e){                    
                return $this->errorResponse('Ошибка заказа', 404, ['sms_phone'=>['Ошибка отправки']]);
            }
        }

    }

    public function createFew(SmsCreateFewRequest $request, SmsService $service)
    {
     
        $keys = $service->checkKeys($request->keys);    

        $results = [];

        if(count($keys) > 2500)return $this->errorResponse('Превышен лимит', 429, ['keys'=>['Превышен лимит: 2500']]);

        foreach ($keys as $order_key) {

            $provider = $service->create($order_key, 1);            

            if($provider){
                try {
                    $results[$order_key] = $service->status_sms_crm($provider->send(0, $request->sms)->status);
                }catch(\Throwable $e){                    
                    $results[$order_key] = 'Ошибка провайдера';
                }
            }else{          
                $results[$order_key] = 'Ошибка заказа';                
            }          
        }

        return ['data'=>$results,'count'=>count($results)];
    }

    public function update($id, SmsService $service)
    {
        return;
    }

    public function delete($id, SmsService $service)
    {
        return;
    }
}
