<?php
namespace App\Services;

use App\Models\User;
use App\Models\Call;
use App\Models\Unload;
use RuntimeException;
use Auth;
use DB;
use App\Queries\PermissionQuery;
use App\Services\AtsQueueService;
use App\Services\OrdersService;
use App\Services\UnloadsService;

class AtsMonitoringService extends Service
{
    const H48 = 172800;
    const H24 = 86400;
    const H6 = 21600;
    const H1 = 3600;
    
    protected $permissionQuery;
    protected $atsQueueService;
    protected $ordersService;
    protected $unloadsService;
    protected $priority1 = [0, 1, 2, 3, 4, 5, 6, 11, 15, 18, 23];
    protected $priority2 = [7, 8, 9, 10, 12, 14, 13, 16, 28, 27];
    protected $priority3 = [17, 20, 21, 22, 24, 25, 26, 30, 31, 32, 19, 29, 33, 34, 35,
                            36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50,
                            51, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, 62, 63, 64];
    protected $call_kinds;
    protected $lags = [
        ['count' => 0, 'orders' => []],
        ['count' => 0, 'orders' => []],
        ['count' => 0, 'orders' => []],
        ['count' => 0, 'orders' => []],
        ['count' => 0, 'orders' => []],
        ['count' => 0, 'orders' => []],
    ];
    protected $new_orders = [
        'not_called' => 0,
        'two'        => 0,
        'three'      => 0,
        'four'       => 0,
        'all'        => 0,
    ];
    protected $orders_by_priority = [
        "1" => ['count' => 0, 'lags' => 0, 'orders' => []],
        "2" => ['count' => 0, 'lags' => 0, 'orders' => []],
        "3" => ['count' => 0, 'lags' => 0, 'orders' => []],
    ];
    protected $first_priority = [
        "all" => 0,
        "2"   => 0,
        "3"   => 0,
        "4"   => 0,
        "5"   => 0,
        "6"   => 0,
        "7"   => 0,
    ];

    public function __construct(
        PermissionQuery $permissionQuery,
        AtsQueueService $atsQueueService,
        OrdersService $ordersService,
        UnloadsService $unloadsService
    )
    {
        $this->permissionQuery = $permissionQuery;
        $this->atsQueueService = $atsQueueService;
        $this->ordersService = $ordersService;
        $this->unloadsService = $unloadsService;
    }
    
    protected function newAttempts($orders)
    {
        $ids = array_map(function($order) {
            return $order['id'];
        }, $orders);
        
        $orders_with_status_group_one = DB::table('orders as o')
            ->whereIn('o.id', $ids)
            ->join('order_status as os', function($join) {
                $join->on('os.order_id', '=', 'o.id')
                    ->where('os.status_type', '=', 1);
            })->pluck('o.id')->toArray();
        
        foreach ($orders as $order) {
            if (!in_array($order['id'], $orders_with_status_group_one)) {
                switch ($order['dial_step']) {
                    case 0:
                    case 1:
                        $this->new_orders['not_called']++;
                        $this->new_orders['all']++;
                        break;
                    
                    case 2:
                        $this->new_orders['two']++;
                        $this->new_orders['all']++;
                        break;
                    
                    case 3:
                        $this->new_orders['three']++;
                        $this->new_orders['all']++;
                        break;
                    
                    default:
                        $this->new_orders['four']++;
                        $this->new_orders['all']++;
                        break;
                }
            }
        }
    }
    
    protected function byPriorities($orders, $queue_id)
    {
        if (!count($orders)) {
            return;
        }
        
        foreach ($orders as $order) {
            $dial_step = $order['dial_step'];
            if       (in_array($dial_step, $this->priority1)) {
                $this->addByPriority($queue_id, $order['id'], 1, $order['dial_time'], $dial_step);
            } elseif (in_array($dial_step, $this->priority2)) {
                $this->addByPriority($queue_id, $order['id'], 2, $order['dial_time']);
            } elseif (in_array($dial_step, $this->priority3)) {
                $this->addByPriority($queue_id, $order['id'], 3, $order['dial_time']);
            }
        }
    }
    
    public function moveUp($orders)
    {
        $result = $this->ordersService->setDialTimes($orders, now());
        return $result;
    }
    
    public function moveDown($orders)
    {
        $result = $this->ordersService->setDialTimes($orders, now()->addHour());
        return $result;
    }
    
    protected function addByPriority($queue_id, $order_id, $priority, $dial_time, $dial_step = null)
    {
        $now = time();
        
        $priority = (string)$priority;
        
        if (!isset($this->orders_by_priority[$priority]['orders'][$queue_id])) {
            $this->orders_by_priority[$priority]['orders'][$queue_id] = [];
        }
        
        $this->orders_by_priority[$priority]['count']++;
        $this->orders_by_priority[$priority]['orders'][$queue_id][] = $order_id;
        
        if ($dial_time <= $now) {
            $this->orders_by_priority[$priority]['lags']++;
        }
        
        if ($priority === "1") {
            $this->first_priority["all"]++;
            if ($dial_step > 1) {
                if ($dial_step >= 7) {
                    $this->first_priority["7"]++;
                } else {
                    $this->first_priority[(string)$dial_step]++;
                }
            }
        }
    }
    
    protected function addLag($queue_id, $order_id, $level, $lag)
    {
        if (!isset($this->lags[$level]['orders'][$queue_id])) {
            $this->lags[$level]['orders'][$queue_id] = [];
        }
        $this->lags[$level]['count']++;
        $this->lags[$level]['orders'][$queue_id][] = $order_id;
    }
    
    public function getLags($queue_id)
    {
        $query = $this->getQueuesQuery($queue_id, 'aq')
            ->join('unloads as u', 'u.id', '=', 'aq.unload_id')
            ->select('aq.*', 'u.api_key as unload_api_key', 'u.config as unload_config');
        $queues = $this->getQueues($queue_id, $query);
        if (!$queues) {
            return false;
        }
        $orders_count = 0;
        $unload_filters = [];
        foreach ($queues as $key => $queue) {
            $unload_config = json_decode($queue->unload_config);
            if (isset($unload_config->filter)) {
                $unload_filters[$queue->id] = $unload_config->filter;
            }
            // return json_decode(DB::table('unloads as u')
            //     ->where('u.api_key', $queue->unload_api_key)
            //     ->first()->config);
            try {
                $orders = $this->ordersService->getOrdersByQueue((array)$queue, $queue->unload_api_key, $this->unloadsService, false);
            } catch(\Throwable $e) {
                $orders = [];
            }
            
            // Считаем общее количество заказов **
            $orders_count += count($orders);
            // По каждому заказу проверяем отставание и добавляем его **
            foreach ($orders as $order) {
                $lag = $order['dial_time'] - time();
                if       ($lag > self::H48) { // Отставание больше 48 часов
                    $this->addLag($queue->id, $order['id'], 5, $lag);
                } elseif ($lag > self::H24) { // Меньше 48, но больше 24 часов
                    $this->addLag($queue->id, $order['id'], 4, $lag);
                } elseif ($lag > self::H6) {  // Меньше 24, но больше 6 часов
                    $this->addLag($queue->id, $order['id'], 3, $lag);
                } elseif ($lag > self::H1) {  // Меньше 6 часов, но больше часа
                    $this->addLag($queue->id, $order['id'], 2, $lag);
                } elseif ($lag > 0) {         // Меньше часа
                    $this->addLag($queue->id, $order['id'], 1, $lag);
                } elseif ($lag <= 0) {        // Текущие или прошедшим временем
                    $this->addLag($queue->id, $order['id'], 0, $lag);
                } else {
                    return $lag;
                }
            }
            
            // Ищем и добавляем новые заказы по текущей очереди
            $this->newAttempts($orders);
            
            $this->byPriorities($orders, $queue->id);
            
        }
        
        return [
            'orders_count'   => $orders_count,
            'lags'           => $this->lags,
            'first_priority' => $this->first_priority,
            'by_priority'    => $this->orders_by_priority,
            'new'            => $this->new_orders,
            'unload_filters' => $unload_filters,
        ];
    }
    
    protected function getQueuesQuery($queue_id, $short = 'aq')
    {
        $this->clearErrors();
        $queue_id = (int)$queue_id; // При SQL иньекции перед значением получим 0 и, 
                                    // соответственно, выдадим только доступные очереди
                                    // При SQL иньекции после значения просто отсекаем лишнее
        $accessed_queue_ids = $this->atsQueueService->getIdsByAccess();
        $query = null;
        if ($queue_id === 0) {
            $query = DB::table('ats_queues as '.$short)
                ->whereIn($short.'.id', $accessed_queue_ids)
                ->where($short.'.type', 'auto')
                ->where($short.'.is_work', true);
        } elseif (in_array($queue_id, $accessed_queue_ids)) {
            $query = DB::table('ats_queues as '.$short)
                ->where($short.'.id', $queue_id)
                ->where($short.'.type', 'auto')
                ->where($short.'.is_work', true);
        } elseif (\App\Models\AtsQueue::find($queue_id)) {
            $this->pushError(["Нет доступа", 403, ['queue_id' => "У вас нет доступа к очереди с ID $queue_id"]]);
            return false;
        } else {
            $this->pushError(["Не найдено", 404, ['queue_id' => "Очереди с ID $queue_id не существует"]]);
            return false;
        }
        
        return $query;
    }
    
    protected function getQueues($queue_id, $query = null)
    {
        if ($query === null) $query = $this->getQueuesQuery($queue_id);
        if ($query) {
            $queues = $query->get();
            if ($queues && $queues->count() == 0) {
                if ($queue_id === 0) {
                    $this->pushError(["Не найдено", 404, ['queue_id' => "Нет доступных очередей"]]);
                    return false;
                } else {
                    $this->pushError(["Неверный ID", 422, ['queue_id' => "Очередь с ID $queue_id не является очередью автодозвона"]]);
                    return false;
                }
            }
            return $queues;
        }
        return false;
    }
    
    protected function getQueueIds($queue_id, $query = null)
    {
        $queue_ids = $this->getQueues($queue_id, $query);
        if ($queue_ids) {
            return $queue_ids->pluck('id')->toArray();
        }
        return false;
    }
    
    public function miniAnalytics($queue_id, $start, $end)
    {
        /**
         * Возвращает список видов звонков, под которые попадает звонок
         * Если не был передан список статусов, то вернёт виды звонков с описаниями
         * @method kind_of_call
         * @param  Array $statuses Список статусов звонка
         * @return Array
         */
        function kind_of_call($statuses = null) {
            $kinds = [
                'no_answer'            => 'Оператор не взял трубку', 
                'answer_later'         => 'Оператор снял через 5 сек и более', 
                'no_operators'         => 'Не было свободных операторов',
                'no_operators_percent' => '% не было свободных операторов ',
                'client_answer'        => 'Клиент взял трубку', 
                'talkings'             => 'Состоялось разговоров',
                'dials'                => 'Всего было вызовов',
            ];
            
            if (!$statuses) {
                return $kinds;
            }
            
            $kind['no_answer']['in']     = ['abandon', 'ringnoanswer'];
            $kind['no_answer']['no']     = ['connect'];
            
            $kind['answer_later']['in']  = ['ringnoanswer', 'connect'];
            $kind['answer_later']['no']  = [];
            
            $kind['no_operators']['in']  = ['abandon'];
            $kind['no_operators']['no']  = ['ringnoanswer', 'connect', 'completecaller', 'completeagent'];
            
            $kind['client_answer']['in'] = ['enterqueue'];
            $kind['client_answer']['no'] = [];
            
            $kind['talkings']['in']      = ['connect'];
            $kind['talkings']['no']      = [];
            
            $call_kinds = [];
            foreach ($kinds as $kind_name => $description) {
                if (empty($kind[$kind_name])) {
                    continue;
                }
                $match_in = count(array_intersect($statuses, $kind[$kind_name]['in'])) == count($kind[$kind_name]['in']);
                $match_no = count(array_intersect($statuses, $kind[$kind_name]['no'])) > 0;
                if ($match_in && !$match_no) {
                    $call_kinds[] = $kind_name;
                }
            }
            return $call_kinds;
        }
        
        $this->call_kinds = kind_of_call();
        
        $queue_ids = $this->getQueueIds($queue_id);
        if ($this->errors()) {
            return false;
        }
        
        $need_call_types = ['in', 'auto', 'auto_reverse'];
        $calls_by_queue = DB::table('calls as c')
            ->where([
                ['c.time', '>=', \Carbon\Carbon::parse("$start 00:00:00")->format("Y-m-d H:i:s")],
                ['c.time', '<=', \Carbon\Carbon::parse("$end 23:59:59")->format("Y-m-d H:i:s")],
            ])
            ->whereIn('c.queue_id', $queue_ids) // FIXME: Раскометировать!!!
            ->whereIn('c.call_type', $need_call_types)
            ->join('call_statuses as cs', 'cs.call_id', '=', 'c.id')
            ->select('c.id as id', 'c.call_type as type', 'cs.status')
            ->get();
        
        // Группируем статусы по звонкам
        $calls=[];
        foreach ($calls_by_queue as $key => $call) {
            if (empty($calls[$call->id])) {
                $calls[$call->id] = ['type' => $call->type, 'statuses' => [], 'kinds' => []];
            }
            $calls[$call->id]['statuses'][] = $call->status;
        }
        
        $result = [];
        foreach ($this->call_kinds as $kind => $description) {
            $result[$kind] = 0;
        }
        
        foreach ($calls as $key => $call) {
            $calls[$key]['kinds'] = kind_of_call($call['statuses']);
            foreach ($calls[$key]['kinds'] as $key => $kind) {
                $result[$kind]++;
            }
        }
        if ($result['client_answer'] == 0) {
            $result['no_operators_percent'] = '0%';
        } else {
            $result['no_operators_percent'] = round($result['no_operators'] / $result['client_answer'] * 100, 1).'%';
        }
        $result['dials'] = count($calls);
        $result['fields'] = $this->call_kinds;
        
        return $result;
    }
    
    public function getOperStates()
    {
        function mb_ucfirst($str) {
            $fc = mb_strtoupper(mb_substr($str, 0, 1));
            return $fc.mb_substr($str, 1);
        }
        
        function oper_name($oper) {
            $data = DB::table('users as u')
                ->where('u.id', $oper->user_id)
                ->join('organizations as o', 'o.id', '=', 'u.organization_id')
                ->select('u.*', 'o.title as org_title')
                ->first();
            
            $middle_name = $data->middle_name ? " " . mb_ucfirst($data->middle_name) : "";
            $oper_name = mb_ucfirst($data->last_name) . " " . mb_ucfirst($data->first_name) . $middle_name . " (" . mb_ucfirst($data->org_title) . ")";
            return $oper_name;
        }
        
        $queues = $this->atsQueueService->getByAccess();
        $queues_ids = DB::table('ats_queues as aq')
            ->whereIn('aq.id', $queues->pluck('id')->toArray())
            ->where('aq.type', 'auto')->pluck('id')->toArray();
            
        $opers = DB::table('ats_queues as aq')
            ->whereIn('aq.id', $queues_ids)
            ->join('lnk_ats_queue__sip_caller_id as aq_cid', 'aq_cid.ats_queue_id', '=', 'aq.id')
            ->join('sip_caller_ids as cid', 'cid.id', '=', 'aq_cid.sip_caller_id_id')
            ->join('ats_users as au', 'au.id', '=', 'cid.ats_user_id')
            ->select('aq.id as queue_id', 'aq.name as queue_name', 'au.id as oper_id', 'au.option_in_call', 'au.user_id')
            ->get();
        
        $result = [];
        foreach ($opers as $oper) {
            if (empty($result[$oper->queue_id])) {
                $result[$oper->queue_id] = [
                    'queue_name' => $oper->queue_name,
                    'queue_id'   => $oper->queue_id,
                    'online'     => ['count' => 0, 'list' => []],
                    'speak'      => ['count' => 0, 'list' => []],
                    'free'       => ['count' => 0, 'list' => []],
                    'away'       => ['count' => 0, 'list' => []]
                ];
            }
            $status = DB::table('user_status_logs as usl')
                ->where('usl.ats_user_id', $oper->oper_id)
                ->join('ats_statuses as s', 's.id', '=', 'usl.status_id')
                ->orderBy('usl.created_at', 'desc')
                ->first();
                
            if ($status) {
                $status_name = mb_strtolower($status->name_en);
                if (!$oper->option_in_call) {
                    if ($status_name == 'speak') {
                        $result[$oper->queue_id]['speak']['count']++;
                        $result[$oper->queue_id]['speak']['list'][] = [
                            'name'=>oper_name($oper),
                            'oic'=>$oper->option_in_call,
                            'status'=>$status_name
                        ];
                    } 
                    if ($status_name == 'online') {
                        $result[$oper->queue_id]['online']['count']++;
                        $result[$oper->queue_id]['online']['list'][] = [
                            'name'=>oper_name($oper),
                            'oic'=>$oper->option_in_call,
                            'status'=>$status_name
                        ];
                    }
                    $result[$oper->queue_id]['away']['count']++;            
                    $result[$oper->queue_id]['away']['list'][] = [
                        'name'=>oper_name($oper),
                        'oic'=>$oper->option_in_call,
                        'status'=>$status_name
                    ];
                }else {
                    if ($status_name == 'online' || $status_name == 'speak') {
                        $result[$oper->queue_id]['online']['count']++;
                        $result[$oper->queue_id]['online']['list'][] = [
                            'name'=>oper_name($oper),
                            'oic'=>$oper->option_in_call,
                            'status'=>$status_name
                        ];
                    }
                    if ($status_name == 'online') {
                        $result[$oper->queue_id]['free']['count']++;
                        $result[$oper->queue_id]['free']['list'][] = [
                            'name'=>oper_name($oper),
                            'oic'=>$oper->option_in_call,
                            'status'=>$status_name
                        ];
                    }
                    if ($status_name == 'speak') {
                        $result[$oper->queue_id]['speak']['count']++;
                        $result[$oper->queue_id]['speak']['list'][] = [
                            'name'=>oper_name($oper),
                            'oic'=>$oper->option_in_call,
                            'status'=>$status_name
                        ];
                    }
                }
            }
        }
        $result_without_keys = [];
        $all = [
            'queue_name' => "Всего",
            'queue_id'   => 0,
            'online'     => ['count' => 0, 'list' => []],
            'speak'      => ['count' => 0, 'list' => []],
            'free'       => ['count' => 0, 'list' => []],
            'away'       => ['count' => 0, 'list' => []]
        ];
        foreach ($result as $item) {
            $result_without_keys[] = $item;
            
            $all['online']['count'] += $item['online']['count'];
            $all['online']['list'] = array_merge($all['online']['list'], $item['online']['list']);
            
            $all['speak']['count'] += $item['speak']['count'];
            $all['speak']['list'] = array_merge($item['speak']['list'], $all['speak']['list']);
            
            $all['free']['count'] += $item['free']['count'];
            $all['free']['list'] = array_merge($item['free']['list'], $all['free']['list']);
            
            $all['away']['count'] += $item['away']['count'];
            $all['away']['list'] = array_merge($item['away']['list'], $all['away']['list']);
        }
        
        // Добавляем пустые очереди (без операторов)
        $empty_queues = array_diff($queues_ids, array_column($result_without_keys, 'queue_id'));
        foreach ($queues as $key => $queue) {
            if (in_array($queue->id, $empty_queues)) {
                $result_without_keys[] = [
                    'queue_name' => $queue->name,
                    'queue_id'   => $queue->id,
                    'online'     => ['count' => 0, 'list' => []],
                    'speak'      => ['count' => 0, 'list' => []],
                    'free'       => ['count' => 0, 'list' => []],
                    'away'       => ['count' => 0, 'list' => []]
                ];
            }
        }
        
        $result_without_keys[] = $all;
        return $result_without_keys;
    }
    
    public function getCurrentCalls()
    {
        function mb_ucfirst($str) {
            $fc = mb_strtoupper(mb_substr($str, 0, 1));
            return $fc.mb_substr($str, 1);
        }
        /******************************
         * Выбираем звонки по очереди *
         ******************************/
         
        $queue_ids = $this->atsQueueService->getIdsByAccess();

        $calls_by_queue = DB::table('calls as c')
            ->where([
                ['c.time', '>', date("Y-m-d H:i:s", strtotime('-15 min'))], // FIXME: Поменять на -15 min
                ['c.disposition', null]
            ])
            ->whereIn('c.call_type', ['auto','in'])
            ->whereIn('c.queue_id', $queue_ids)
            ->get();
        
        /***********************************
         * Выбираем звонки по пользователю *
         ***********************************/

        $orgs = $this->permissionQuery->getAllAccessOrganizationIDs(Auth::user()->organization_id, true);
        $users_ids = DB::table('users')
            ->whereIn('users.organization_id', $orgs)
            ->pluck('id')->toArray();
        
        $calls_by_user = DB::table('calls as c')
            ->where([
                ['c.time', '>', date("Y-m-d H:i:s", strtotime('-15 min'))], // FIXME: Поменять на -15 min
                ['c.disposition', null],
                ['c.call_type', 'out']
            ])
            ->whereIn('c.user_id', $users_ids)
            ->get();
        
        /***************************
         * Выбираем звонки по сипу *
         ***************************/

        $sips = DB::table('users')
            ->whereIn('users.organization_id', $orgs)
            ->join('ats_users as au', function($join) {
                $join->on('au.user_id', '=', 'users.id')->where('au.type', 'privat');
            })
            ->join('sip_caller_ids as cid', 'cid.ats_user_id', '=', 'au.id')
            ->pluck('caller_id')->toArray();
        
        $calls_by_sip = DB::table('calls as c')
            ->where([
                ['c.time', '>', date("Y-m-d H:i:s", strtotime('-15 min'))], // FIXME: Поменять на -15 min
                ['c.disposition', null],
                ['c.call_type', 'out']
            ])
            ->whereIn('c.sip', $sips)
            ->get();
        
        /*******************************
         * Дополняем данными по связям *
         *******************************/

        $calls = $calls_by_queue->merge($calls_by_user)->merge($calls_by_sip)->sortBy('time')->values()->all();
        
        // Фильтруем дубли
        $unique_calls = [];
        foreach ($calls as $call) $unique_calls[$call->id] = $call;
        $calls = collect(array_values($unique_calls));
        
        foreach ($calls as $call) {
            // Название очереди
            if ($call->queue_id) {
                $queue = DB::table('ats_queues')->where('id', $call->queue_id)->first();
                if ($queue) {
                    $call->queue_name = $queue->name;
                }
            } else {
                $call->queue_name = "";
            }
            
            // Ключ заказа
            if ($call->order_id) {
                $order = DB::table('orders')->where('id', $call->order_id)->first();
                if ($order) {
                    $call->order_key = $order->key;
                }
            } else {
                $call->order_key = "";
            }
            
            // Имя оператора
            $call_status = DB::table('call_statuses as cs')
                ->where('call_id', $call->id)
                ->orderBy('time', 'desc')
                ->first();
            if ($call_status && $call_status->user_id) {
                $call->call_status = $call_status;
                $last_oper = DB::table('users as u')
                    ->where('u.id', $call_status->user_id)
                    ->join('organizations as o', 'o.id', '=', 'u.organization_id')
                    ->select('u.*', 'o.title as org_title')
                    ->first();
                
                $middle_name = $last_oper->middle_name ? " " . mb_ucfirst($last_oper->middle_name) : "";
                $call->oper_name = mb_ucfirst($last_oper->last_name) . " " . mb_ucfirst($last_oper->first_name) . $middle_name . " (" . mb_ucfirst($last_oper->org_title) . ")";
            } else {
                $call->call_status = null;
                $call->oper_name = "";
            }
        }
        // return [
        //     '$calls_by_user' => $calls_by_user,
        //     '$calls_by_sip' => $calls_by_sip,
        //     '$calls_by_queue' => $calls_by_queue,
        //     '$queue_ids' => $queue_ids,
        //     '$orgs' => $orgs,
        //     '$users_ids' => $users_ids,
        // ];
        return $calls;
    }
    
    protected function ordersByPriority($orders)
    {
        $priorities = [
            1 => ['count' => 0, 'orders' => []],
            2 => ['count' => 0, 'orders' => []],
            3 => ['count' => 0, 'orders' => []]
        ];

        if (count($orders) > 0) {
            foreach ($orders as $order) {
                // $o[] = $order['id'];
                $dial_step = $order['dial_step'];
                if (in_array($dial_step, $this->priority1)) {
                    $priorities[1]['count']++;
                    $priorities[1]['orders'][] = $order['id'];
                } elseif (in_array($dial_step, $this->priority2)) {
                    $priorities[2]['count']++;
                    $priorities[2]['orders'][] = $order['id'];
                } elseif (in_array($dial_step, $this->priority3)) {
                    $priorities[3]['count']++;
                    $priorities[3]['orders'][] = $order['id'];
                }
            }
        }
        
        return $priorities;
    }
    
    public function getDialCoeff()
    {
        $query = $this->getQueuesQuery(0, 'aq')
            ->join('unloads as u', 'u.id', '=', 'aq.unload_id')
            ->select('aq.*', 'u.api_key as unload_api_key');
        $queues = $this->getQueues(0, $query);
        if (!$queues) {
            return false;
        }
        
        $result = [];
        foreach ($queues as $queue) {
            
            /*********************************
             * Считаем заказы по приоритетам *
             *********************************/
            
            try {
                $orders = $this->ordersService->getOrdersByQueue((array)$queue, $queue->unload_api_key, $this->unloadsService);
            } catch(\Throwable $e) {
                $orders = [];
            }
            $priorities = $this->ordersByPriority($orders);
            
            /*****************************
             * Считаем операторов онлайн *
             *****************************/
            
            $logs = DB::table('queue_state_history as qsh')
                ->where([
                    ['qsh.queue_id', $queue->id],
                    ['qsh.created_at', '>', date("Y-m-d H:i:s", strtotime('-15 min'))]
                ]);
            $speak  = floatval($logs->avg('speak'));
            $online = floatval($logs->avg('online')) + $speak;
            
            /**********************************
             * Считаем коэффициент и нагрузку *
             **********************************/
            
            $capacity = 0;
            $coeff = 0;
            if ($online > 1) {
                $capacity = $speak / $online * 100;
                $coeff = (($priorities[1]['count'] * 1.0) + ($priorities[2]['count'] * 0.7) + ($priorities[3]['count'] * 0.4)) / round($online, 1);
            } else {
                $coeff = (($priorities[1]['count'] * 1.0) + ($priorities[2]['count'] * 0.7) + ($priorities[3]['count'] * 0.4));
            }
            
            $result[] = [
                'coeff' => round($coeff, 1),
                'operators' => round($online, 1),
                'capacity' => round($capacity, 1),
                'queue' => $queue->name,
            ];
        }
        return $result;
    }
    
    protected function getSearchRepository()
    {
        return null;
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