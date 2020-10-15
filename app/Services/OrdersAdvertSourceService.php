<?php
/**
 * Created by PhpStorm.
 * User: timur
 * Date: 14.02.19
 * Time: 15:54
 */

namespace App\Services;


use App\Libraries\ExportToExcel;
use App\Models\User;
use App\Queries\PermissionQuery;
use App\Repositories\OrderAdvertSourceRepository;
use App\Repositories\OrdersRepository;

class OrdersAdvertSourceService extends Service
{
    protected $orderAdvertSourceRepository;
    protected $ordersRepository;
    protected $rolesService;

    protected $permissionQuery;
    protected $exportToExcel;

    public function __construct(
        OrderAdvertSourceRepository $orderAdvertSourceRepository,
        OrdersRepository $ordersRepository,
        RolesService $rolesService,

        PermissionQuery $permissionQuery,
        ExportToExcel $exportToExcel
    )
    {
        $this->orderAdvertSourceRepository = $orderAdvertSourceRepository;
        $this->ordersRepository = $ordersRepository;
        $this->rolesService = $rolesService;

        $this->permissionQuery = $permissionQuery;
        $this->exportToExcel = $exportToExcel;
    }

    public function create($data, $reindex = false)
    {
        $orderAdvertSource = $this->orderAdvertSourceRepository->create($data);

        if ($orderAdvertSource) {
            if ($reindex) {
                $this->orderAdvertSourceRepository->reindexModel($orderAdvertSource, true);
            }

            return $orderAdvertSource;
        }

        return false;
    }

    protected function getSearchRepository()
    {
        return $this->orderAdvertSourceRepository;
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