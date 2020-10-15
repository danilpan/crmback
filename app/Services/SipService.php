<?php
namespace App\Services;

use App\Repositories\SipRepository;
use App\Models\Sip;
use App\Models\User;
use RuntimeException;
use Auth;
use App\Services\SipCallerIdService;
use App\Queries\SipPermissionQuery as PermissionQuery;

class SipService extends Service
{
    protected $repository;
    protected $permissionQuery;
    protected $cidService;

    public function __construct(SipRepository $repository, PermissionQuery $permissionQuery, SipCallerIdService $cidService)
    {
        $this->repository = $repository;
        $this->permissionQuery = $permissionQuery;
        $this->cidService = $cidService;
    }
    
    public function index(){
        $list = $this->repository->all();
        return $list;
    }
    
    public function create($data, $reindex = false)
    {
        $sip = $this->repository->create($data);

        if ($sip) {
            if ($reindex) {
                $this->repository->reindexModel($sip, true);
            }        
            $cid = $this->cidService->create(['sip_id'=>$sip->id, 'caller_id'=>$sip->login], true);
            return $sip;
        }
        return false;
    }

    public function update($id, $data, $reindex = false)
    {
        $sip = null;
        
        $data = $this->repository->update($data, $id);
        if ($data) {
            $sip = $this->repository->find($id);
            
            if ($reindex) {
                $this->repository->reindexModel($sip, true);
            }
        }
        
        return $data;
    }
    
    public function getAllAccessCallerIDs($view_children, $only_id = false, $filter = [])
    {
        if ($only_id) {
            return $this->permissionQuery->getAllAccessCallerIDs(Auth::user()->organization_id, $view_children, $filter)->pluck('id')->toArray();
        } else {
            return $this->permissionQuery->getAllAccessCallerIDs(Auth::user()->organization_id, $view_children, $filter);
        }
    }
    
    public function organizationsList($with_children)
    {
        return $this->permissionQuery->getAllAccessCompanyIDs(Auth::user()->organization_id, $with_children);
    }

    protected function getSearchRepository()
    {
        return $this->repository;
    }

    protected function addSearchConditions(User $user=null, array $filters=null)
    {
        return $filters;
    }

    protected function getPermissionQuery(){
        return $this->permissionQuery;
    }

    protected function getExportToExcelLib(){
        return null;
    }
}