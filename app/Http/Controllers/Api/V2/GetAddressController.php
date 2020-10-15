<?php
namespace App\Http\Controllers\Api\V2;

use App\Http\Requests\Api\V2\SearchRequest;
use App\Services\GetAddressService;
use App\Repositories\GeoRepository;

class GetAddressController extends Controller
{

    public function getList(SearchRequest $request, GetAddressService $service, GeoRepository $geoRepository){

        if(empty($request['geo']))
            return $this->errorResponse('Не указано гео', 404, ['full_address'=>'Не указано гео']);

        if(!in_array($request['geo'],['KZ','RU','UA']))
            return $this->errorResponse('Не поддерживаемое гео', 404, ['full_address'=>'Не поддерживаемое гео']);

        $address = $service->GetAddress($request['q'], $request['geo']);        

        return [
            'data'=>$address,
            'total'=>count($address)
        ];

    }

    public function getListWarehouse(SearchRequest $request, GetAddressService $service){

        $warehouse = $service->select_warehouse($request['warehouse_id']);

        return [
            'data'=>$warehouse,
            'total'=>count($warehouse)
        ];

    }
    
}
