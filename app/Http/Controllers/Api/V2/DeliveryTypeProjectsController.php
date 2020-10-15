<?php
namespace App\Http\Controllers\Api\V2;

use App\Http\Requests\Api\V2\DeliveryTypeProjectsRequest;
use App\Repositories\DeliveryTypesProjectsRepository;
use App\Http\Requests\Api\V2\SearchRequest;
use App\Models\DeliveryTypeProjects;
use App\Services\DeliveryTypeProjectsService;

class DeliveryTypeProjectsController extends Controller
{


     public function getByDeliveryTypeId($id, DeliveryTypeProjectsService $service){
        $items = $service->getByDeliveryTypeId($id);
        return $items;
    }


     public function create(DeliveryTypeProjectsRequest $request, DeliveryTypeProjectsService $service)
    {
        $data = $request->validated();
        $item = $service->create($data);

        return $item;
    }

    public function update($id, $delivery_id, $geo_id, DeliveryTypeProjectsService $service)
    {        
        return $service->delete($id, $delivery_id, $geo_id);
    }
}
