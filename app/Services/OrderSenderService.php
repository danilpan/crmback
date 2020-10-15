<?php
namespace App\Services;

use App\Libraries\ExportToExcel;
use App\Models\User;
use App\Queries\PermissionQuery;
use App\Repositories\OrderSenderRepository;

class OrderSenderService extends Service
{
    protected $orderSenderRepository;
    protected $permissionQuery;
    protected $exportToExcel;
    protected $userService;

    public function __construct(
        OrderSenderRepository $orderSenderRepository,
        PermissionQuery $permissionQuery,
        ExportToExcel $exportToExcel
    )
    {
        $this->orderSenderRepository = $orderSenderRepository;
        $this->permissionQuery = $permissionQuery;
        $this->exportToExcel = $exportToExcel;
    }

    public function create($data, $reindex = false){
        $orderSender = $this->orderSenderRepository->create($data);

        if($orderSender){
            if($reindex){
                $this->orderSenderRepository->reindexModel($orderSender, $reindex);
            }

            return $orderSender;
        }
        return false;
    }

    public function update($id, $data, $reindex){
        $orderSender = $this->orderSenderRepository->update($data, $id);

        if($orderSender){
            if($reindex){
                $this->orderSenderRepository->reindexModel($orderSender, $reindex);
            }

            return $orderSender;
        }

        return false;
    }

    public function delete($id, $reindex){
        $order_sender = $this->orderSenderRepository->find($id);

        if($order_sender){
            if($reindex) {
                $this->orderSenderRepository->deleteFromIndex($order_sender);
            }
            return $this->orderSenderRepository->delete($id);
        }
        return false;
    }

    protected function getExportToExcelLib()
    {
        return $this->exportToExcel;
    }
    protected function getPermissionQuery()
    {
        return $this->permissionQuery;
    }
    protected function getSearchRepository()
    {
        return $this->orderSenderRepository;
    }

    public function addSearchConditions(User $user = null, array $filters = null)
    {
        return $filters;
    }
}