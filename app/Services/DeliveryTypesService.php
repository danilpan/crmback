<?php
namespace App\Services;

use App\Repositories\DeliveryTypesRepository;
use App\Repositories\OrdersRepository;
use App\Models\User;
use App\Services\RolesService;
use Auth;

use App\Queries\PermissionQuery;
use App\Libraries\ExportToExcel;

class DeliveryTypesService extends Service
{
    protected $deliveryTypesRepository;
    protected $ordersRepository;
    protected $rolesService;
    
    protected $permissionQuery;
    protected $exportToExcel;

    public function __construct(
        DeliveryTypesRepository $deliveryTypesRepository,
        OrdersRepository $ordersRepository,
        RolesService $rolesService,

        PermissionQuery $permissionQuery,
        ExportToExcel $exportToExcel
    )
    {
        $this->deliveryTypesRepository = $deliveryTypesRepository;
        $this->ordersRepository = $ordersRepository;
        $this->rolesService = $rolesService;

        $this->permissionQuery = $permissionQuery;
        $this->exportToExcel = $exportToExcel;
    }    

    public function getByOrderKey($key, $is_can_view_hidden_delivery_type)
    {
        $filters = [];        
        $all_filters = [];  
        
        $all_filters['constant_score']['filter']['bool']['must'][]['match']['is_work'] = true;
        $all_delivery_types = $this->deliveryTypesRepository->searchByParams(
            $all_filters, 
            ['organization_id'=>'asc'],
            1,250,false
        );      

        if($key=='new_order'){

            $orgz_arr = $this->rolesService->getArrChildOrgsByOrganizationId(Auth::user()->organization_id);

            $filters['constant_score']['filter']['bool']['must'][]['terms']['organization_id'] = $orgz_arr;
            $filters['constant_score']['filter']['bool']['must'][]['match']['is_work'] = true;
            if(!$is_can_view_hidden_delivery_type)
                $filters['constant_score']['filter']['bool']['must'][]['match']['is_show'] = true;
            
            $delivery_types = $this->deliveryTypesRepository->searchByParams(
                $filters, 
                ['organization_id'=>'asc'],
                1,250,false
            );

        }else{

            $order_info = $this->ordersRepository->searchByParams(
                ['match' => [
                    'key' => $key]
                ], 
                ['key'=>'asc']
            )->toArray();  

            $order_project_ids = [];
            foreach ($order_info[0]['projects'] as $project) {
                $order_project_ids[] = $project['id'];
            }
            $order_geo_id = $order_info[0]['geo']['id'];
            $current_delivery_type = $order_info[0]['delivery_type']['id'];
            
            $orgz_arr = $this->rolesService->getArrChildOrgsByOrganizationId($order_info[0]['organization_id']);
            
            $filters['constant_score']['filter']['bool']['should'][0]['bool']['must'][]['terms']['organization_id'] = $orgz_arr;
            $filters['constant_score']['filter']['bool']['should'][0]['bool']['must'][]['match']['is_work'] = true;

            if(!$is_can_view_hidden_delivery_type)
                $filters['constant_score']['filter']['bool']['must'][]['match']['is_show'] = true;

            if(!is_null($order_info[0]['delivery_types_id']))
                $filters['constant_score']['filter']['bool']['should'][1]['match']['id'] = $order_info[0]['delivery_types_id'];                                       
            $delivery_types = $this->deliveryTypesRepository->searchByParams(
                $filters, 
                ['organization_id'=>'asc'],
                1,250,false
            );      

        }        

        if($key != 'new_order') {
            $delivery_types_by_progect_and_geo = [];
            $current_is_by_project_and_geo = false;
            $current_dt = null;
            foreach ($all_delivery_types as $item) {
                if ($item->id == $current_delivery_type) $current_dt = $item;
                foreach ($item->projects as $key => $pro) {
                    if ($pro['geo_id'] == $order_geo_id && in_array($pro['project_id'], $order_project_ids)) {
                        $delivery_types_by_progect_and_geo[] = $item;
                        if ($item->id == $current_delivery_type) {
                            $current_is_by_project_and_geo = true;
                        }
                    }
                }
            }
            
            if (count($delivery_types_by_progect_and_geo) > 0) {
                $delivery_types = $delivery_types_by_progect_and_geo;
                if (!$current_is_by_project_and_geo && $current_dt) {
                    $delivery_types[] = $current_dt;
                }
                $delivery_types = collect($delivery_types);
            }
        }
        
        $delivery_types = $delivery_types->map(function($item) {
            if($item->is_work=='0'){
                $item->disabled = true;                
            };
            return $item;
        });

        return $delivery_types;
    }

    protected function getSearchRepository()
    {
        return $this->deliveryTypesRepository;
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
