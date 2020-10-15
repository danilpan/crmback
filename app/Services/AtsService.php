<?php
namespace App\Services;

use App\Repositories\AtsRepository;
use App\Models\Ats;
use App\Models\User;
use RuntimeException;
use Auth;
use Illuminate\Support\Facades\DB;
use App\Queries\PermissionQuery;

class AtsService extends Service
{
    protected $repository;
    protected $permissionQuery;
        
    
    public function __construct(AtsRepository $repository, PermissionQuery $permissionQuery)
    {
        $this->repository = $repository;
        $this->permissionQuery = $permissionQuery;
    }    

    public function getAll(){
        $list = $this->repository->all();
        return $list;
    }
    
    public function create($data, $reindex = false)
    {
        $ats = $this->repository->create($data);

        if ($ats) {
            if ($reindex) {
                $this->repository->reindexModel($ats, true);
            }
            return $ats;
        }
        return false;
    }

    public function update($id, $data, $reindex = false)
    {
        $ats = null;
        
        $data = $this->repository->update($data, $id);
        if ($data) {
            $ats = $this->repository->find($id);
            
            if ($reindex) {
                $this->repository->reindexModel($ats, true);
            }
        }
        
        return $data;
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
    
    /**
     * Генерирует md5-хэш случайной строки длинной 2048 символов
     * @return string md5-хэш (строка 32 символа)
     */
    public function generateApiKey()
    {
        $str = "";
        for ($i=0; $i < 2048; $i++) {
            $str .= chr(rand(1,255));
        }

        return md5($str);
    }
    
    /**
     * Проверка данных от фронта на наличие и корректность поля key (API-ключ)
     * @param  Array $data данные от фронта прошедшие через валидатор
     * @return Array данные со скорректированным key
     */
    public function checkData($data)
    {
        if (array_key_exists('key', $data)) {
            if (strlen($data['key']) != 32) {
                $data['key'] = $this->generateApiKey();
            }
        } else {
            $data['key'] = $this->generateApiKey();
        }
        
        return $data;
    }
    
    public function reconfigure($id)
    {
        $prepare = function ($arr, $generate_id = false, $is_queue = false) {
            $res = [];
            $id = 1;
            foreach ($arr as $item) {
                if ($generate_id) {
                    $item->id = $id;
                }
                if ($is_queue) {
                    $item->steps  = base64_encode($item->steps);
                    $item->steps2 = base64_encode($item->steps2);
                }
                $res[$item->id] = $item;
                $id++;
            }
            return base64_encode(json_encode($res));
        };
        
        $data = [];
        
        $item = Ats::find($id);
        if (!$item) {
            $this->pushError(["Not found", 404, 'id' => "ATS server with ID $id not found"]);
            return false;
        }
        
        $rules = DB::table('ats_groups as g')
            ->where('g.ats_id', $id)
            ->join('out_routes as or', 'or.ats_group_id', '=', 'g.id')
            ->select(
                'or.id',
                'or.name',
                'or.comment',
                'or.mask',
                'or.replace_count',
                'or.prefix',
                'or.trunks1 as trunks',
                'or.trunks_p1',
                'or.trunks2',
                'or.trunks_p2',
                'or.ats_group_id'
            )->get();
        $data['rules'] = $prepare($rules);
        unset($rules);
        
        $trunks = DB::table('ats_groups as g')
            ->where('g.ats_id', $id)
            ->join('sips as s', 's.ats_group_id', '=', 'g.id')
            ->select(
                's.id',
                's.host',
                's.port',
                's.passwd',
                's.login',
                's.max_channels',
                's.template',
                's.connect_type',
                's.description as comment',
                's.is_work'
            )->get();
        $data['trunks'] = $prepare($trunks);
        unset($trunks);
            
        $queues = DB::table('ats_groups as g')
            ->where('g.ats_id', $id)
            ->join('ats_queues as q', 'q.ats_group_id', '=', 'g.id')
            ->select(
                'q.id',
                'q.type',
                'q.name',
                'q.organization_id as id_organization',
                'q.ats_group_id',
                'q.unload_id as unload',
                'q.steps1 as steps',
                'q.steps2',
                'q.off_time1 as off_time_1',
                'q.off_time2 as off_time_2',
                'q.strategy',
                'q.how_call',
                'q.is_work',
                'q.check_wbt'
            )->get();
        $data['queues'] = $prepare($queues, false, true);
        unset($queues);
            
        $queue_agents = DB::table('ats_groups as g')
            ->where('g.ats_id', $id)
            ->join('ats_users as au', 'au.ats_group_id', '=', 'g.id')
            ->join('sip_caller_ids as cid', 'cid.ats_user_id', '=', 'au.id')
            ->join('lnk_ats_queue__sip_caller_id as a', 'a.sip_caller_id_id', '=', 'cid.id')
            ->select(
                'cid.id',
                'a.ats_queue_id as id_queue',
                'au.id as id_operator',
                'cid.caller_id as sip',
                'a.sorting',
                'au.option_in_call as option_in_calls'
            )->get();
        $data['queue_agents'] = $prepare($queue_agents, true);
        unset($queue_agents);
            
        $queue_trunks_independent = DB::table('ats_groups as g')
            ->where('g.ats_id', $id)
            ->join('ats_users as au', function($join) {
                $join->on('au.ats_group_id', '=', 'g.id')
                    ->where('au.type', 'independent');
            })
            ->join('sip_caller_ids as cid', 'cid.ats_user_id', '=', 'au.id')
            ->select(
                'cid.id',
                'cid.ats_queue_id as id_queue',
                'cid.caller_id as phone'
            );
        
        $queue_trunks = DB::table('ats_groups as g')
            ->where('g.ats_id', $id)
            ->join('sips as s', 's.ats_group_id', '=', 'g.id')
            ->join('sip_caller_ids as cid', 'cid.sip_id', '=', 's.id')
            ->select(
                'cid.id',
                'cid.ats_queue_id as id_queue',
                'cid.caller_id as phone'
            )->union($queue_trunks_independent)->get();
        $data['queue_trunks'] = $prepare($queue_trunks);
        unset($queue_trunks_independent);
        unset($queue_trunks);
        
        $users = DB::table('ats_groups as g')
            ->where('g.ats_id', $id)
            ->join('ats_users as au', 'au.ats_group_id', '=', 'g.id')
            ->select(
                'au.id',
                'au.port',
                'au.passwd',
                'au.login',
                'au.max_channels',
                'au.ats_group_id',
                'au.type',
                'au.comment',
                'au.option_in_call',
                'au.out_calls',
                'au.is_work'
            )->get();
        $data['users'] = $prepare($users);
        unset($users);
            
        $unloads = DB::table('ats_groups as g')
            ->where('g.ats_id', $id)
            ->join('ats_queues as q', 'q.ats_group_id', '=', 'g.id')
            ->join('unloads as u', 'u.id', '=', 'q.unload_id')
            ->select(
                'u.id',
                'u.organization_id as id_organization',
                'u.name',
                'u.comment',
                'u.api_key as key',
                'u.is_work'
            )->get();
        $data['queue_unloads'] = $prepare($unloads);
        unset($unloads);
            
        $caller_ids = DB::table('ats_groups as g')
            ->where('g.ats_id', $id)
            ->join('ats_users as au', 'au.ats_group_id', '=', 'g.id')
            ->join('sips as s', 's.ats_group_id', '=', 'g.id')
            ->crossJoin('sip_caller_ids as cid')
            ->select('cid.*')
            ->get();
        $data['caller_ids'] = $prepare($caller_ids);
        unset($caller_ids);
        unset($prepare);
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://$item->ip/aster_api/APIAsterisk.php?act=update_full_options&key=$item->key",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data)
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        
        return response()->json(['data' => $response]);
    }
    
    public static function getByKey($key)
    {
        return Ats::where('key', $key)->first();
    }
}