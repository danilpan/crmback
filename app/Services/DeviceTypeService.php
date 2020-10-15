<?php
/**
 * Created by PhpStorm.
 * User: timur
 * Date: 18.02.19
 * Time: 17:21
 */

namespace App\Services;


use App\Libraries\ExportToExcel;
use App\Models\User;
use App\Queries\PermissionQuery;
use App\Repositories\DeviceTypeRepository;
use App\Repositories\OrdersRepository;

class DeviceTypeService extends Service
{
    protected $deviceTypeRepository;
    protected $ordersRepository;
    protected $rolesService;

    protected $permissionQuery;
    protected $exportToExcel;

    public function __construct(
        DeviceTypeRepository $orderAdvertSourceRepository,
        OrdersRepository $ordersRepository,
        RolesService $rolesService,

        PermissionQuery $permissionQuery,
        ExportToExcel $exportToExcel
    )
    {
        $this->deviceTypeRepository = $orderAdvertSourceRepository;
        $this->ordersRepository = $ordersRepository;
        $this->rolesService = $rolesService;

        $this->permissionQuery = $permissionQuery;
        $this->exportToExcel = $exportToExcel;
    }

       public function create($data, $reindex = false)
        {
            $deviceType = $this->deviceTypeRepository->create($data);

            if ($deviceType) {
                if ($reindex) {
                    $this->deviceTypeRepository->reindexModel($deviceType, true);;
                }

                return $deviceType;
            }

            return false;
        }

    protected function getSearchRepository()
    {
        return $this->deviceTypeRepository;
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