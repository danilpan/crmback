<?php
namespace App\Services;

use App\Repositories\AtsUserRepository;
use App\Models\AtsUser;
use App\Models\Ats;
use App\Models\User;
use App\Models\SipCallerId;
use App\Models\History;
use RuntimeException;
use Auth;
use DB;
use App\Queries\PermissionQuery;

class AtsUserService extends Service
{
    protected $repository;
    protected $permissionQuery;

    public function __construct(AtsUserRepository $repository, PermissionQuery $permissionQuery)
    {
        $this->repository = $repository;
        $this->permissionQuery = $permissionQuery;
    }
    
    public function writeHistory($changes, $id){

        if(isset(Auth::user()->id)){
            $user_id = Auth::user()->id;
        }else{
            $user_id = 1;
        }
        
        if(!empty($changes))
            History::create([
                'reference_table' => $this->repository->model(),
                'reference_id'    => $id,
                'actor_id'        => $user_id,
                'body'            => json_encode(['main' => $changes],JSON_UNESCAPED_UNICODE)
            ]);
    }
    
    public function index($request, $with_children = false){
        $orgs = $this->permissionQuery->getAllAccessCompanyIDs(Auth::user()->organization_id, $with_children);
        $filter = json_decode($request['filter']);
        
        $add_filter = [];
        $i=1;
        foreach ($orgs as $org) {            
            $add_filter[] = ["organizations.id", "=", $org];
            if($i != (count($orgs)))$add_filter[] = "or";
            $i++;
        } 

        if(!empty($filter)){                
            if (!in_array("and", $filter)) {                   
                    $temp_filter = $filter;
                    $filter = [];
                    $filter = [$temp_filter];
            }              
            $filter[] = "and";
        }
        $filter[] = $add_filter;        
        
        /*$filter = '[';
        foreach ($orgs as $org) {
            if ($filter != '[') $filter .= ',"or",';
            $filter .= '["organizations.id","=",'.$org.']';
        }
        $filter .= ']';*/
        $request['filter'] = json_encode($filter);

        $list = $this->dxSearch($request);
        // $list = $this->repository->all();
        return $list;
    }
    
    public function create($data, $reindex = false)
    {
        $this->clearErrors();
        if ($data['type'] == "privat") {
            if (!key_exists('cid', $data)) {
                $this->pushError(['Error', 422, ['cid'=>'Для пользователя типа privat необходимо указать CallerID']]);
                return false;
            }
            if (!is_int((int)$data['cid']) || (int)$data['cid'] < 1001) {
                $this->pushError(['Error', 422, ['cid'=>'Для пользователя типа privat CallerID должен быть числом от 1001 до 9999']]);
                return false;
            }
            $cid = SipCallerId::where('caller_id', $data['cid'])->first();
            $cid_value = $data['cid'];
        } elseif ($data['type'] == "independent") {
            $cid = SipCallerId::where('caller_id', $data['login'])->first();
        }
        unset($data['cid']);
        $ats_user = $this->repository->create($data);

        if ($ats_user) {
            if ($reindex) {
                $this->repository->reindexModel($ats_user, true);
            }
            if ($ats_user->type == "privat") {
                if (!$cid) {
                    $cid = new SipCallerId;
                    $cid->caller_id = $cid_value;
                    $cid->ats_user_id = $ats_user->id;
                    $cid->save();
                } else {
                    $cid->ats_user_id = $ats_user->id;
                    $cid->save();
                }
            } elseif ($ats_user->type == "independent") {
                if (!$cid) {
                    $cid = new SipCallerId;
                    $cid->caller_id = $ats_user->login;
                    $cid->ats_user_id = $ats_user->id;
                    $cid->save();
                } else {
                    $cid->ats_user_id = $ats_user->id;
                    $cid->save();
                }
            }
            $this->writeHistory($ats_user->attributesToArray(), $ats_user->id);
            return $ats_user;
        }
        return false;
    }

    public function update($id, $data, $reindex = false)
    {
        $item = null;
        
        $attributes = $this->repository->find($id)->attributesToArray();
        $changes = [];
        foreach ($data as $key => $value) {
            if ($data[$key] != $attributes[$key]) {
                $changes[$key] = $value;
            }
        }
        
        $data = $this->repository->update($data, $id);
        if ($data) {
            $item = $this->repository->find($id);            
            if ($reindex) {
                $this->repository->reindexModel($item, true);
            }
            
            $this->writeHistory($changes, $item->id);
        }
        
        return $item;
    }
    
    public function isOnline()
    {
        $operator_id = Auth::user()->id;
        
        $ats_users = DB::table('ats_users as au')
            ->where('au.user_id', $operator_id)
            ->join('user_status_logs as l', 'l.ats_user_id', '=', 'au.id')
            ->join('ats_statuses as as', 'as.id', '=', 'l.status_id')
            ->select(
                'au.id',
                'au.user_id',
                'au.login',
                'au.user_id',
                'as.name_ru',
                'as.name_en',
                'l.created_at',
                'au.option_in_call'
                )
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()->toArray();
            
        $statuses = [];
        foreach (array_reverse($ats_users) as $user) {
            $statuses[$user->id] = $user;
        }
        
        foreach ($statuses as $status) {
            $status_name = mb_strtolower($status->name_en);
            if ($status_name == "online" || $status_name == "speak") {
                return $status;
            }
        }
        
        return null;
    }
    
    public function inCallsSwitch($val)
    {
        $this->clearErrors();
        
        $ats_user = $this->isOnline();
        if (!$ats_user) {
            $this->pushError(['Not found', 404, ['Не найден активный пользователь АТС']]);
            return false;
        }
        
        $ats_user = AtsUser::where('id', $ats_user->id)->with('atsGroup')->first();
        $ats = Ats::find($ats_user->atsGroup->ats_id);
        $ats_user->option_in_call = $val == 0 ? false : true;
        $ats_user->save();
        $url = "http://$ats->ip/aster_api/APIAsterisk.php?key=$ats->key";
        $data = [
            'act'     => "comment_queue_agents",
            'comment' => $val,
            'agent'   => $ats_user->login,
        ];
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data)
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    public static function getUserByCallerId($caller_id){
        $sipCallerId = SipCallerId::where('caller_id', $caller_id)->first();
        if($sipCallerId){
            $sipCallerId = $sipCallerId->atsUser()->with('user')->first();
            if($sipCallerId){                
                return $sipCallerId->user;
            }       
        }        
        return null;
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