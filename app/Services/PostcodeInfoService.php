<?php
namespace App\Services;

use App\Repositories\PostcodeInfoRepository;
use App\Repositories\OrdersRepository;
use App\Models\DeliveryType;
use App\Models\User;
use Auth;

class PostcodeInfoService extends Service
{
    protected $PostcodeInfoRepository;    
    
    public function __construct(PostcodeInfoRepository $PostcodeInfoRepository, OrdersRepository $OrdersRepository)
    {
        $this->PostcodeInfoRepository = $PostcodeInfoRepository;
        $this->OrdersRepository = $OrdersRepository;
    }

    public function getPostcodeInfos($request){

        if($request['key']=='new_order'){
            $organization_id = Auth::user()->organization_id;
        }else{        
            $order_info = $this->OrdersRepository->searchByParams(
                ['match' => [
                    'key' => $request['key']               ]
                ], 
                ['key'=>'asc']
            )->toArray();

            $organization_id = $order_info[0]['organization_id'];
        }

        $delivery_types = DeliveryType::where(['organization_id' => $organization_id, 'is_work'=>'1'])->pluck('id')->toArray(); 
        
        $filter['must']['bool']['must'][]['term']['postcode'] = $request['postcode'];
        $filter['must']['bool']['must'][]['terms']['delivery_type_id'] = $delivery_types;
        $query['constant_score']['filter']['bool']['must'] = $filter['must'];

        $data = $this->PostcodeInfoRepository->searchByParams(
            $query, 
            ['id'=>'asc']);

        $postcode_infos = [];
        
        $postcode_infos = $data->map(function($item) {                        
            return [
                'text' => 'Комментарий: '.$item['comment'].'. Время доставки в днях: '.$item['time'],
                'price' => $item['price'],
                'delivery_type_id' => $item['delivery_type_id']
            ];                    
        }); 

        return $postcode_infos;

    }
   
    protected function getSearchRepository()
    {
        return $this->PostcodeInfoRepository;
    }

    protected function addSearchConditions(User $user=null,array $filters=null)
    {
        return $filters;
    }

    public function getPermissionQuery(){
        return $this->permissionQuery;
    }

    public function getExportToExcelLib(){
        return $this->exportToExcel;
    }

}
