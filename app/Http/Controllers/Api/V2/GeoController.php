<?php
namespace App\Http\Controllers\Api\V2;

use App\Http\Requests\Api\V2\GeoRequest;
use App\Repositories\GeoRepository;
use App\Http\Requests\Api\V2\SearchRequest;
use App\Services\GeoService;
use App\Models\Geo;

class GeoController extends Controller
{


    public function getList(SearchRequest $request, GeoService $service){
        
        //$geo = $geoRepository->all();

        return $service->getAll();

        //return $this->search($request, $service);

/*        $page           = $request->get('page', 1);
        $perPage        = $request->get('per_page', 20);
        $sortKey        = $request->get('sort_key', 'id');
        $sortDirection  = $request->get('sort_direction', 'desc');

        $geo = $geoRepository->search($page, $perPage, $sortKey, $sortDirection);

        

        return $geo;*/
    }

    public function dxGeos(SearchRequest $request, GeoService $service){
        return  $service->dxSearch($request);
    }

    public function getById($id, GeoRepository $geoRepository)
    {
        $geo = $geoRepository->find($id);

        return $geo;
    }

    public function getByPhone($phone, GeoService $service)
    {        
        $geo = $service->getByPhone($phone);
        if(!empty($geo)){
            return $geo;
        }else{
            return $this->errorResponse('Не найдено', 422, ['geo_by_phone'=>'Гео не определно']);;    
        }     
    }

    public function create(GeoRequest $request, GeoRepository $repository)
    {
        $data = $request->validated();
        $geo = $repository->create($data);

        return $geo;
    }

    public function update($id, GeoRequest $request, GeoRepository $repository)
    {
        $geo = $repository->update( $request->validated(), $id, "id");

        return $geo;
    }

    public function delete($id, GeoRepository $repository)
    {
        return $repository->delete($id);
    }
}
