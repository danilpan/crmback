<?php
/**
 * Created by PhpStorm.
 * User: timur
 * Date: 14.02.19
 * Time: 19:17
 */

namespace App\Http\Controllers\Api\V2;


use App\Http\Requests\Api\V2\OrderAdvertSourceRequest;
use App\Http\Requests\Api\V2\SearchRequest;
use App\Repositories\OrderAdvertSourceRepository;
use App\Services\OrdersAdvertSourceService;

class OrderAdvertSourceController extends Controller
{
    protected $relations  = ['order'];


    public function getList(SearchRequest $request, OrdersAdvertSourceService $service)
    {
        //$request = $service->dxAddPermissions($request, $this->auth->user()['organization_id']);
        return  $service->dxSearch($request);
    }

    public function exToExcel(SearchRequest $request, OrdersAdvertSourceService $service)
    {
        return  response()->file($service->exToExcel($request));
    }
/* 
      public function getList(SearchRequest $request, OrderAdvertSourceRepository $orderAdvertSourceRepository)
     {

         $orderAdvertSources = $orderAdvertSourceRepository->all();

         return $orderAdvertSources;
     } */


    public function getById($id, OrderAdvertSourceRepository $orderAdvertSourceRepository)
    {
        $order_advert_source = $orderAdvertSourceRepository->find($id);
        return $order_advert_source;
    }

    public function getByOrderKey($key, OrdersAdvertSourceService $service)
    {

        $orderAdvertSourceRepository = $service->getByOrderKey($key);

        return ['total'=>$orderAdvertSourceRepository->count(), 'data' => $orderAdvertSourceRepository];

    }

    public function create(OrderAdvertSourceRequest $request, OrderAdvertSourceRepository $repository)
    {
        $data = $request->validated();
        $item = $repository->create($data);
        $repository->reindexModel($item, true);
        return $item;
    }

    public function update($id, OrderAdvertSourceRequest $request, OrderAdvertSourceRepository $repository)
    {
        $data   = $request->validated();
        $order_advert_source  = $repository->update($data, $id, "id");
        if($order_advert_source){
            $data['id'] = $id;
            $repository->reindexModel($order_advert_source, true);
            return $data;
        }else{
            return $order_advert_source;
        }
    }

    public function delete($id, OrderAdvertSourceRepository $repository)
    {
        return $repository->delete($id);
    }
}