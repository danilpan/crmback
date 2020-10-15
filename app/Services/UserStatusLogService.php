<?php
namespace App\Services;

use App\Models\UserStatusLog;
use App\Models\User;
use App\Models\AtsUser;
use App\Models\AtsStatus;
use RuntimeException;
use Auth;
use App\Queries\PermissionQuery;

class UserStatusLogService extends Service
{
    protected $permissionQuery;

    public function __construct(PermissionQuery $permissionQuery, AtsService $atsService)
    {
        $this->atsService = $atsService;
        $this->permissionQuery = $permissionQuery;
    }
    
    public function index(){
        $list = $this->repository->all();
        return $list;
    }
    
    public function create($data)
    {
        $user_status_log = UserStatusLog::create($data);

        if ($user_status_log) {
            return $user_status_log;
        }
        return false;
    }

    public function update($id, $data)
    {
        $data = UserStatusLog::update($data, $id);
        return $data;
    }
    
    public function refresh($data)
    {
        // $arr = [
        //     "2290" => [
        //         "agent" => "2290",
        //         "state" => "online"
        //     ],
        //     "4358" => [
        //         "agent" => "4358",
        //         "state" => "offline"
        //     ]
        // ];
        // return base64_encode(json_encode($arr));
        $this->clearErrors();
        $ats = AtsService::getByKey($data['key']);
        if (!$ats) {
            $this->pushError(["Not found", 404, ["key" => "Ats with key ".$data['key']." not found"]]);
            return false;
        }
        $data = json_decode(base64_decode($data['arr']));
        $statuses_all = AtsStatus::all();
        foreach ($statuses_all as $status) {
            $statuses[mb_strtolower($status->name_en)] = $status;
        }
        $updated = 0;
        foreach ($data as $value) {
            $agent = AtsUser::where('login', $value->agent)->first();
            if ($agent) {
                $status = UserStatusLog::where('ats_user_id', $agent->id)->orderBy('created_at', 'desc')->first();
                if (!$status || $statuses[mb_strtolower($value->state)]->id != $status->status_id) {
                    $this->create(["ats_user_id"=>$agent->id, "status_id" => $statuses[mb_strtolower($value->state)]->id]);
                    $updated++;
                }
            }            
        }
        return "updated:$updated";
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