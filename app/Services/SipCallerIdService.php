<?php
namespace App\Services;

use App\Repositories\SipCallerIdRepository;
use App\Models\SipCallerId;
use App\Models\AtsQueue;
use App\Models\User;
use RuntimeException;
use Auth;
use Illuminate\Support\Collection;
use App\Queries\SipCallerIdPermissionQuery as PermissionQuery;

class SipCallerIdService extends Service
{
    protected $repository;
    protected $permissionQuery;

    public function __construct(SipCallerIdRepository $repository, PermissionQuery $permissionQuery)
    {
        $this->repository = $repository;
        $this->permissionQuery = $permissionQuery;
    }
    
    public function index(){
        $list = $this->repository->all();
        return $list;
    }
    
    public function create($data, $reindex = false)
    {
        $sip_caller_id = $this->repository->create($data);

        if ($sip_caller_id) {
            if ($reindex) {
                $this->repository->reindexModel($sip_caller_id, true);
            }
            return $sip_caller_id;
        }
        return false;
    }

    public function update($id, $data, $reindex = false)
    {
        $sip_caller_id = null;
        
        $data = $this->repository->update($data, $id);
        if ($data) {
            $sip_caller_id = $this->repository->find($id);
            
            if ($reindex) {
                $this->repository->reindexModel($sip_caller_id, true);
            }
        }
        
        return $data;
    }
    
    public function getFreePrivate()
    {
        $reg = "'^[1-9]{1}[0-9]{3}$'";
        $item = SipCallerId::whereRaw("caller_id ~ $reg")->whereNull('ats_user_id')->whereNull('sip_id')->first();
        if ($item) {
            return $item->caller_id;
        } else {
            $exists = SipCallerId::whereRaw("caller_id ~ $reg")->pluck('caller_id')->toArray();
            $new = rand(1001, 9999);
            while (in_array($new, $exists)) {
                $new = rand(1001, 9999);
            }
            return $new;
        }
    }
    
    public function getForIn($with_children)
    {
        return $this->permissionQuery->getForIn(Auth::user()->organization_id, $with_children);
    }
    
    /**
     * Description
     * @method getOperators
     * @param  integer    $ats_queue_id    ID of AtsQueue
     * @param  integer    $organization_id ID of Company
     * @return Collection
     */
    public function getOperators($ats_queue_id, $organization_id)
    {
        $this->clearErrors();
        
        $queue = AtsQueue::find($ats_queue_id);
        if (!$queue) {
            $this->pushError(['Not found', 404, ['Очередь не найдена']]);
            return false;
        }
        
        $current_time = time();
        // Для тестирования
        // $current_time = strtotime(date('Y-m-d')  ." 06:00");
        // $current_time = strtotime(date('Y-m-d')  ." 10:00");
        // $current_time = strtotime(date('Y-m-d')  ." 21:00");
        // $current_time = strtotime(date('Y-m-d')  ." 23:00");
        
        $off_begin = strtotime(date('Y-m-d')  ." ". $queue->off_time1);
        if (strtotime($queue->off_time1) <= strtotime($queue->off_time2)) {
            $off_end      = strtotime(date('Y-m-d') ." ". $queue->off_time2);
            $prev_off_end = strtotime(date('Y-m-d') ." ". $queue->off_time2 . "-1 day");
        } else {
            $off_end      = strtotime(date('Y-m-d') ." ". $queue->off_time2 . "+1 day");
            $prev_off_end = strtotime(date('Y-m-d') ." ". $queue->off_time2);
        }
        
        $is_work = true;
        
        if (($current_time >= $off_begin && $current_time <= $off_end) || 
            ($current_time < $off_begin && $current_time < $prev_off_end)) {
            $is_work = false;
        }
        
        // Для тестирования
        // return [
        //          "current"   => date('Y-m-d H:i', $current_time), 
        //          "off_begin" => date('Y-m-d H:i', $off_begin), 
        //          "of_end"    => date('Y-m-d H:i', $off_end), 
        //          "status"    => !$is_work ? "off" : "work"
        //       ];
        $opers = $this->permissionQuery->getOperators($queue->type, $queue->ats_group_id, $is_work, $organization_id);
        $opers_array = [];
        foreach ($opers as $key => $oper) {
            $oper->title = preg_replace("/\s{2,}/", " ", "$oper->last_name $oper->first_name $oper->middle_name ($oper->organization) [$oper->caller_id]");
            $opers_array[] = $oper;
        }
        return collect($opers_array);
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