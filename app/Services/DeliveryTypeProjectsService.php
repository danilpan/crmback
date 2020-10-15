<?php
namespace App\Services;

use App\Repositories\DeliveryTypesRepository;
use App\Models\User;

use App\Queries\PermissionQuery;
use App\Libraries\ExportToExcel;
use DB;

class DeliveryTypeProjectsService extends Service
{
    protected $deliveryTypesRepository;

    protected $permissionQuery;
    protected $exportToExcel;
    
    public function __construct(
        DeliveryTypesRepository $deliveryTypesRepository,

        PermissionQuery $permissionQuery,
        ExportToExcel $exportToExcel
        )
    {
        $this->deliveryTypesRepository= $deliveryTypesRepository;

        $this->permissionQuery = $permissionQuery;
        $this->exportToExcel = $exportToExcel;
    }


    public function create($data)
    {
        $deliveryType = $this->deliveryTypesRepository->find($data['id']);

        if ($deliveryType) {
           
            $deliveryType->projects()->attach([$data['project'] => ['geo_id' => $data['geo']]]);

            return $deliveryType;
        }

        return false;
    }

    public function getByDeliveryTypeId($id)
    {
        $deliveryType = $this->deliveryTypesRepository->find($id);

        if ($deliveryType) {          
            
            return $deliveryType->projects()->withPivot('geo_id')->get();

        }

        return false;
    }

    public function delete($id, $delivery_id, $geo_id)
    {

        $cond = [['project_id', $id], ['delivery_type_id', $delivery_id]];
        if ((int)$geo_id > 0) $cond[] = ['geo_id', $geo_id];
        $dtp = DB::table('delivery_type_project')->where($cond)->first();
        $result = DB::table('delivery_type_project')->where($cond)->delete();
        return response()->json(['data' => $dtp, 'result' => $result]);
    }



    protected function getSearchRepository()
    {
        return $this->projectsRepository;
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
