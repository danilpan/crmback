<?php
namespace App\Services\Optovichok;

use App\Libraries\ExportToExcel;
use App\Models\User;
use App\Queries\PermissionQuery;
use App\Repositories\Optovichok\ClientRepository;
use App\Services\RolesService;
use App\Services\Service;

class ClientsService extends Service
{
    protected $clientRepository;
    protected $rolesService;

    protected $permissionQuery;
    protected $exportToExcel;

    public function __construct(
        ClientRepository $clientRepository,
        RolesService $rolesService,

        PermissionQuery $permissionQuery,
        ExportToExcel $exportToExcel
    )
    {
        $this->clientRepository = $clientRepository;
        $this->rolesService = $rolesService;

        $this->permissionQuery = $permissionQuery;
        $this->exportToExcel = $exportToExcel;
    }

    public function getById($id)
    {
        $client = null;

        if($this->clientRepository->find($id)) {
            $client = $this->clientRepository->find($id);
        }

        return $client;
    }

    public function create($data, $reindex = false)
    {
        $client = $this->clientRepository->create($data);

        if ($client) {
            if ($reindex) {
                $this->clientRepository->reindexModel($client, true);
            }

            return $client;
        }

        return false;
    }

    public function update($id, $data, $reindex = false)
    {
        $client = $this->clientRepository->update($data, $id);
        $result = null;
        if ($client) {
            if ($reindex) {
                $this->clientRepository->reindexModel($client, true);
            }
            $result = $client;
        }

        return $result;
    }

    public function delete(int $id)
    {
        $client = $this->clientRepository->find($id);
        if ($client) {
            $this->clientRepository->deleteFromIndex($client);

            return $this->clientRepository->delete($id);
        }

        return false;
    }

    protected function getSearchRepository()
    {
        return $this->clientRepository;
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