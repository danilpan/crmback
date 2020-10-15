<?php
/**
 * Created by PhpStorm.
 * User: timur
 * Date: 18.02.19
 * Time: 17:17
 */

namespace App\Http\Controllers\Api\V2;


use App\Http\Requests\Api\V2\DeviceTypeRequest;
use App\Http\Requests\Api\V2\SearchRequest;
use App\Repositories\DeviceTypeRepository;
use App\Services\DeviceTypeService;

class DeviceTypeController extends Controller
{
    protected $relations = ['orders'];

    public function getList(SearchRequest $request, DeviceTypeService $service)
    {
        return  $service->dxSearch($request);
    }

    public function exToExcel(SearchRequest $request, DeviceTypeService $service)
    {
        $request = $service->dxAddPermissions($request, $this->auth->user()['organization_id']);
        return  response()->file($service->exToExcel($request));
    }

/*       public function getList(SearchRequest $request, DeviceTypeRepository $deviceTypeRepository)
     {

        $deviceTypes = $deviceTypeRepository->all();

         return $deviceTypes;
     }
 */
    public function getById($id, DeviceTypeRepository $deviceTypeRepository)
    {

        $deviceType = $deviceTypeRepository->find($id);
        return $deviceType;
    }

    public function getByOrderKey($key, DeviceTypeService $service)
    {
        $device_types = $service->getByOrderKey($key);


        return ['total'=>$device_types->count(), 'data' => $device_types];

    }

    public function create(DeviceTypeRequest $request, DeviceTypeRepository $repository)
    {
        $data = $request->validated();
        $item = $repository->create($data);
        $repository->reindexModel($item, true);

        return $item;
    }

    public function update($id, DeviceTypeRequest $request, DeviceTypeRepository $repository)
    {
        $data   = $request->validated();
        $device_type  = $repository->update($data, $id, "id");
        if($device_type){
            $data['id'] = $id;
            $repository->reindexModel($device_type, true);
            return $data;
        }else{
            return $device_type;
        }
    }

    public function delete($id, DeviceTypeRepository $repository)
    {
        return $repository->delete($id);
    }
}