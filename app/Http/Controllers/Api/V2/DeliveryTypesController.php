<?php
namespace App\Http\Controllers\Api\V2;

use App\Repositories\DeliveryTypesRepository;
use App\Http\Requests\Api\V2\DeliveryTypeUpdateRequest;
use App\Services\DeliveryTypesService;
use App\Http\Requests\Api\V2\SearchRequest;
use App\Http\Requests\Api\V2\DeliveryTypesRequest;
use DB;
use Auth;

class DeliveryTypesController extends Controller
{
    protected $relations    = ['project', 'site', 'gasket'];


    public function getList(SearchRequest $request, DeliveryTypesService $service)
    {

        if(isset($request['group'])){
            $result =  $service->dxSwitchGroup($request);
            if(isset($result))
                return $result;
        }   

        $request = $service->dxAddPermissions($request, $this->auth->user()['organization_id']);
        return  $service->dxSearch($request);
    }

    public function exToExcel(SearchRequest $request, DeliveryTypesService $service)
    {
        $request = $service->dxAddPermissions($request, $this->auth->user()['organization_id']);
        return  response()->file($service->exToExcel($request)); 
    }

   /*  public function getList(SearchRequest $request, DeliveryTypesRepository $DeliveryTypesRepository)
    {

       $DeliveryTypes = $DeliveryTypesRepository->all();

        return $DeliveryTypes;
    } */

    public function getById($id, DeliveryTypesRepository $DeliveryTypesRepository)
    {

        $delivery      = $DeliveryTypesRepository->find($id);        
        return $delivery;
    }

    public function getByOrderKey($key, DeliveryTypesService $service)
    {

        $is_can_view_hidden_delivery_type=$this->userService->can("menu.main.orders.can_view_hidden_delivery_type", $this->auth->user()['organization_id']);

        $delivery_types = $service->getByOrderKey($key, $is_can_view_hidden_delivery_type);

        //dd($delivery_types);

        return ['total'=>$delivery_types->count(), 'data' => $delivery_types];

    }

    public function create(DeliveryTypesRequest $request, DeliveryTypesRepository $repository)
    {  
        $data = $request->validated();
        $item = $repository->create($data);
        $repository->reindexModel($item, true);

        return $item;
    }    

    public function update($id, DeliveryTypesRequest $request, DeliveryTypesRepository $repository)
    {
        $data   = $request->validated();
        $delivery  = $repository->update($data, $id, "id");
        if($delivery){
            $data['id'] = $id;
                $repository->reindexModel($delivery, true);
            return $data;
        }else{
            return $delivery;
        }
    }

    public function delete($id, DeliveryTypesRepository $repository)
    {
        return $repository->delete($id);
    }
}
