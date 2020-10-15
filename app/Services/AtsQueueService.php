<?php
namespace App\Services;

use App\Repositories\AtsQueueRepository;
use App\Models\AtsQueue;
use App\Models\User;
use App\Models\History;
use App\Models\SipCallerId;
use App\Models\Unload;
use App\Models\AtsGroup;
use App\Models\Ats;
use RuntimeException;
use Auth;
use Illuminate\Support\Facades\DB;
use App\Services\SipCallerIdService;
use App\Queries\AtsQueuePermissionQuery as PermissionQuery;

class AtsQueueService extends Service
{
    protected $repository;
    protected $permissionQuery;
    protected $callerService;

    public function __construct(AtsQueueRepository $repository, PermissionQuery $permissionQuery, SipCallerIdService $callerService)
    {
        $this->repository = $repository;
        $this->permissionQuery = $permissionQuery;
        $this->callerService = $callerService;
    }
    
    public function getByAccess($is_work = true, $page = 1, $size = 50)
    {
        $orgs = $this->permissionQuery->getAllAccessCompanyIDs(Auth::user()->organization_id, true);
        $filters['constant_score']['filter']['bool']['should'][]['terms']['organizations.id'] = $orgs;
        $filters['constant_score']['filter']['bool']['should'][]['terms']['organization_id'] = $orgs;
        if ($is_work === "true" || $is_work === true) {
            $filters['constant_score']['filter']['bool']['must'][]['term']['is_work'] = $is_work;
        }
        
        $queues = $this->repository->searchByParams($filters, ['id'=>'asc'], $page, $size, true);
        return $queues;
    }
    
    public function getIdsByAccess($is_work = true, $page = 1, $size = 50)
    {
        return $this->getByAccess($is_work, $page, $size)->pluck('id')->toArray();
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
    
    public function index($request){
        $is_work = $request['is_work'];
        $size = $request['take'] ? $request['take'] : 50;
        $page = $request['skip'] ? (int)round($request['skip'] / $size) + 1 : 1;
        return $this->getByAccess($is_work, $page, $size);
    }
    
    public function getByCompany($id, $request, $with_other){
        if ($with_other) {
            $filter = '[["organization_id","=",'.$id.'],"or",["organizations.id","=",'.$id.']]';
        } else {
            $filter =  '["organization_id","=",'.$id.']';
        }        
        
        $request['filter'] = '[' . $filter . ',"and",["is_work","=","true"]]';

        $list = $this->dxSearch($request);
        return $list; 
    }
    
    public function create($data, $reindex = false)
    {
        $data['organization_id'] = Auth::user()->organization_id;
        
        $data['avr'] = $this->prepareAvr(array_key_exists('avr', $data) ? $data['avr'] : null);
        if ($this->errors()) {
            return false;
        }
        
        $item = $this->repository->create($data);

        if ($item) {
            $item->check_wbt = (int)$item->check_wbt == 0 ? false : true;
            $item->is_work = (int)$item->is_work == 0 ? false : true;
            if ($reindex) {
                $this->repository->reindexModel($item, true);
            }
            $this->writeHistory($item->attributesToArray(), $item->id);
            return $item;
        }
        return false;
    }
    
    /**
     * Пытается переместить загруженный файл AVR в постоянное хранилище
     * @method prepareAvr
     * @param  UploadedFile $file Загруженный файл
     * @return mixed Ссылка на файл, "null" если передана стока или false в случае ошибки
     */
    protected function prepareAvr($file)
    {
        $dir = 'audio/avr/';
        $url = $file;
        if ($file && !is_string($file)) {
            $allowed_mimes = ['mpeg', 'mp3', 'x-mp3'];
            $finfo = finfo_open(FILEINFO_MIME);
            $mime = explode("/", explode(";",finfo_file($finfo, $file->getPathName()))[0]);
            if ($mime[0] == 'audio' && in_array($mime[1], $allowed_mimes)) {
                $path = public_path($dir);
                if (!file_exists($path) || !is_dir($path)) {
                    if (!mkdir($path, 0755, true)) {
                        $this->pushError(["Нет каталога", 500, ["avr" => ["Не удалось создать каталог $path"]]]);
                        unlink($file->getPathName());
                        return false;
                    }
                }
                $filename = md5($file->getClientOriginalName());
                $ext = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
                if (rename($file->getPathName(), "$path$filename.$ext")) {
                    return asset("$dir$filename.$ext");
                }
            } else {
                $this->pushError(["Ошибка", 422, ["avr" => ["Недопустимый формат файла"]]]);
                unlink($file->getPathName());
                return false;
            }
            unlink($file->getPathName());
        }
        return $url;
    }

    public function update($id, $data, $can_edit_options, $reindex = false)
    {
        $this->clearErrors();
        $item = null;
        
        $attributes = $this->repository->find($id)->attributesToArray();
        $changes = [];
        $errors = [];
        $permission_required = [
            'steps1', 
            'steps2', 
            'off_time1', 
            'off_time2', 
            'check_wbt', 
            'how_call', 
            'unload_id',
            'ats_group_id'
        ];
        
        foreach ($data as $key => $value) {
            if ($data[$key] != $attributes[$key]) {
                if (!$can_edit_options && in_array($key, $permission_required)) {
                    $errors[$key] = "У вас нет доступа на редактирование $key очередей АТС";
                }
                $changes[$key] = $value;
            }
        }
        
        if (count($errors) > 0) {
            $this->pushError(["Нет доступа", 403, $errors]);
            return false;
        }
        
        $data['avr'] = $this->prepareAvr(array_key_exists('avr', $data) ? $data['avr'] : null);
        if ($this->errors()) {
            return false;
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
    
    public function attachTrunks($id, $callers)
    {
        $this->clearErrors();
        $item = $this->repository->find($id);
        if (!$item) {
            $this->pushError(['Не найдено', 404, ['item'=>'AtsQueue с ID '.$id.' не существует']]);
            return false;
        }
        
        if ($item->type != 'in') {
            $this->pushError(["Ошибка", 422, ["Транки можно привязывать только к входящим очередям"]]);
            return false;
        }
        
        $attached = $item->callerIdsIn()->get();
        foreach ($attached as $caller) {
            if (empty($callers) || !in_array($caller->id, $callers)) {
                $caller->ats_queue_id = null;
                $caller->save();
            }
        }
        
        $can_attach = $this->callerService->getForIn(true)->pluck('id')->toArray();
        $errors = [];
        $i = 0;
        foreach ($callers as $cid) {
            $caller = SipCallerId::find($cid);
            if (!$caller->ats_queue_id){
                $caller->ats_queue_id = $id;
                $caller->save();
            } elseif ($caller->ats_queue_id != $id) {                    
                if (!in_array($caller->id, $can_attach)) {
                    $errors["caller_ids.$i"] = "Нельзя привязать $caller->id:'$caller->caller_id' к этой очереди";
                } else {
                    $errors["caller_ids.$i"] = "Транк $caller->id уже привязан к очереди $caller->ats_queue_id";
                }
            }
            $i++;
        }
        
        if (count($errors) > 0) {
            $this->pushError(["Привязаны не все транки", 207, $errors]);
        }
        return true;
    }
    
    public function canAttachCompanies($id, $companies)
    {
        $this->clearErrors();
        $item = $this->repository->find($id);
        if (!$item) {
            $this->pushError(['Не найдено', 404, ['item'=>'AtsQueue с ID '.$id.' не существует']]);
            return false;
        }
        
        $attached = $item->organizations()->pluck('id')->toArray();
        $accessed = $this->permissionQuery->getAllAccessCompanyIDs(Auth::user()->organization_id, true);
        $errors = [];
        foreach ($attached as $org) {
            if (!in_array($org, $accessed) && !in_array($org, $companies)) {
                $errors["$org"] = "У вас нет доступа к организации с ID $org, поэтому нельзя отменить привязку";
            }
        }
        $i = 0;
        foreach ($companies as $org) {
            if (!in_array($org, $accessed) && !in_array($org, $attached)) {
                $errors["companies.$i"] = "У вас нет доступа к организации с ID $org, поэтому её нельзя привязать";
            }
            $i++;
        }
        
        if (count($errors) > 0) {
            $this->pushError(["Ошибка доступа", 403, $errors]);
        }
        return count($errors) == 0;
    }

    public static function getAtsQueueByUnloadsKey($api_key=''){        
        $unload = Unload::where('api_key', $api_key)->first();
        if($unload){
            return $unload->atsQueue()->first();   
        };
        return $unload;
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
    
    public function reconfigure($id)
    {
        $item = AtsQueue::find($id);
        if (!$item) {
            $this->pushError(["Not found", 404, 'id' => "ATS queue with ID $id not found"]);
            return false;
        }
            
        $queue_operators = DB::table('lnk_ats_queue__sip_caller_id as q_cid')
            ->where('q_cid.ats_queue_id', $id)
            ->join('sip_caller_ids as cid', 'cid.id', '=', 'q_cid.sip_caller_id_id')
            ->join('ats_users as au', 'au.id', '=', 'cid.ats_user_id')
            ->select(
                'q_cid.ats_queue_id as id_queue',
                'au.id as id_operator',
                'cid.caller_id as sip',
                'q_cid.sorting',
                'au.option_in_call as option_in_calls'
            )
            ->get();
        
        if ($item->type == "in") {
            $queue = [
                'name'            => $item->name,
                'type'            => $item->type,
                'off_time_1'      => $item->off_time1,
                'off_time_2'      => $item->off_time2,
                'id_organization' => $item->organization_id,
                'id'              => $item->id,
                'ats_group_id'    => $item->ats_group_id,
            ];
        
            $queue_trunks = DB::table('sip_caller_ids as cid')
                ->where('cid.ats_queue_id', $id)
                ->join('sips as s', 's.id', '=', 'cid.sip_id')
                ->select(
                    's.id',
                    's.host',
                    's.port',
                    's.passwd',
                    's.login',
                    's.max_channels',
                    // 's.id_organization',
                    // 's.taken_sites',
                    's.template',
                    's.connect_type',
                    // 's.is_registred',
                    // 's.state',
                    's.description as comment',
                    's.is_work',
                    'cid.ats_queue_id as id_queue'
                )
                ->get();
        } else {
            $unload = DB::table('unloads as u')
                ->where('u.id', $item->unload_id)
                ->select('id', 'api_key as key')
                ->first();
                
            // FIXME: В этом массиве есть статические значения, пока так, но это не нормально
            $queue = [
                'name'            => $item->name,
                'type'            => $item->type,
                'unload'          => [
                    $unload->id => $unload,
                ],
                'strategy'        => $item->strategy,
                'off_time_1'      => $item->off_time1,
                'off_time_2'      => $item->off_time2,
                'steps'           => base64_encode($item->steps1),
                'steps2'          => base64_encode($item->steps2),
                'is_work'         => true, // FIXME: На данный момент очередь не имеет поля is_work
                'check_wbt'       => $item->check_wbt,
                'how_call'        => $item->how_call,
                'forward_call'    => 0,
                'upload_api_key'  => '4b41573343434a54bd82961cd2a4br',
                'id'              => $item->id,
                'queue_id'        => $item->id,
                'update_user_id'  => 13732,
                'managers'        => [
                    $queue_operators,
                ],
                'id_organization' => $item->organization_id,
                'ats_group_id'    => $item->ats_group_id,
            ];
            
            $queue_trunks = [];
        }
            
        // return [
        //         'queue_trunks' => $queue_trunks,
        //         'queue_operators' => $queue_operators,
        //         'queue' => $queue,
        //     ];
            
        $prepare = function ($arr, $generate_id = false) {
            $res = [];
            $id = 1;
            foreach ($arr as $item) {
                if ($generate_id) {
                    $item->id = $id;
                }
                $res[$item->id] = $item;
                $id++;
            }
            return json_encode($res);
        };
        
        $ats = DB::table('ats_groups as ag')
            ->where('ag.id', $item->ats_group_id)
            ->join('ats as a', 'a.id', '=', 'ag.ats_id')
            ->select('a.*')->groupBy('a.id')->first();
        $url = "http://$ats->ip/aster_api/APIAsterisk.php?key=$ats->key";
            
        $data = [
            'act'             => $item->type == 'in' ? 'create_queue_new' : 'update_queue_new',
            'queue'           => json_encode($queue),
            'queue_operators' => json_encode($queue_operators),
            'queue_trunks'    => $prepare($queue_trunks),
        ];
        
        // return $data;
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data)
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        
        return response()->json(['data' => json_decode($response)]);
    }
}