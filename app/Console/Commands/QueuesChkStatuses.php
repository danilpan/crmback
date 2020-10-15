<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\OrdersService;
use App\Services\UnloadsService;
use App\Models\Order;
use App\Models\History;
use App\Repositories\OrdersRepository;
use DB;

class QueuesChkStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:chk_statuses';
    protected $ordersService;
    protected $unloadsService;
    protected $ordersRepository;
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Команда берет заказы, которые прозваниваются в очередях и проверяет достигнут ли последний шаг, указанный в настройках. Если у заказа шаг прозвона в очереди превысил или равен последнему шагу в настройках ему установливается статус указанный в настройках.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        OrdersService $ordersService,
        UnloadsService $unloadsService,
        OrdersRepository $ordersRepository
    )
    {
        parent::__construct();
        $this->ordersRepository = $ordersRepository;
        $this->ordersService = $ordersService;
        $this->unloadsService = $unloadsService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $queues = DB::table('ats_queues as aq')
            ->where([
                ['aq.type', 'auto'],
                ['aq.is_work', true],
            ])
            ->join('unloads as u', 'u.id', '=', 'aq.unload_id')
            ->select('aq.*', 'u.api_key as unload_api_key')
            ->get();
        
        $collection = [];
        
        foreach ($queues as $queue) {
            if (!$queue->last_status && !$queue->dsday) continue;
            
            $last_steps = [
                1 => 0,
                2 => 0
            ];
            
            // Находим последний шаг веба в очереди 
            $steps1 = json_decode($queue->steps1);
            foreach ($steps1 as $key => $step) {
                if ($step->step > $last_steps[1]) $last_steps[1] = $step->step;
            }
            
            // Если шаги раздельные, то находим последний шаг партнёра в очереди
            if ($queue->check_wbt) {
                $steps2 = json_decode($queue->steps2);
                foreach ($steps2 as $key => $step) {
                    if ($step->step > $last_steps[2]) $last_steps[2] = $step->step;
                }
            }
            
            $last_status = DB::table('statuses as s')->where('s.id', $queue->last_status)->first();
            $dsday = DB::table('statuses as s')->where('s.id', $queue->dsday)->first();
            
            // Получаем заказы по фильтру очереди
            try {
                $orders = $this->ordersService->getOrdersByQueue((array)$queue, $queue->unload_api_key, $this->unloadsService, false);
            } catch(\Throwable $e) {
                $orders = [];
            }
            
            // Перебираем заказы
            foreach ($orders as $order) {
                // dump($queue->id . ' - ' . $order['id']);
                // Определяем последний шаг для текущего заказа
                $last_step = null;
                $wbt = is_null($order['webmaster_type']) ? 1 : $order['webmaster_type'];
                if ($queue->check_wbt) {
                    $last_step = $last_steps[$wbt];
                } else {
                    $last_step = $last_steps[1];
                }
                
                $last_status_checked_model = null;
                $dsday_checked_model = null;
                if ($last_status) { // Чекаем last_status
                    $last_status_checked_model = $this->checkLastStatus($order, $last_step, $last_status);
                }
                if ($dsday) { // Чекаем dsday
                    $dsday_checked_model = $this->checkDSDay($order, $dsday);
                }
                
                if ($last_status_checked_model) {
                    $collection[] = $last_status_checked_model;
                }
                if ($dsday_checked_model && !$last_status_checked_model) {
                    $collection[] = $dsday_checked_model;
                }
            }
        }
        if(!empty($collection)) {
            echo "Реиндексация " . count($collection) . " заказов...\n";
            $this->ordersRepository->reindexByData(collect($collection));
        }
        echo "Проверка статусов завершена\n";
    }
    
    protected function setStatus($order_id, $order_key, $status, $chk_by = 'id')
    {
        switch ($chk_by) {
            case 'id':
                $where = [
                    'status_id' => $status->id,
                    'order_id' => $order_id
                ];
                break;
            
            case 'type':
                $where = [
                    'status_type' => $status->type,
                    'order_id' => $order_id
                ];
                break;
        }
        
        // Установлен ли уже нужный статус
        $status_exists = DB::table('order_status')
            ->where($where)
            ->count() > 0 ? true : false;
        
        if ($status_exists) {
            // echo "Заказу " . $order_key . " уже устанавлен статус \"$status->title\"\n";
            return false;
        }
        
        echo "Заказу " . $order_key . " устанавливаем статус \"$status->title\"\n";
        
        // Удаляем все статусы того же типа, что и полученный статус
        DB::table('order_status')
            ->where([
                'status_type' => $status->type,
                'order_id' => $order_id
            ])
            ->delete();
        
        // Устанавливаем статус
        $model = Order::find($order_id);
        $model->statuses()->attach($status->id, [
            'user_id' => 1,
            'status_type' => $status->type,
            'created_at'=>\Carbon\Carbon::now()->format('Y-m-d H:i:s')
        ]);
        
        // Пишем запись в историю заказа
        $this->writeHistory($model->id, $status);
        
        return true;
    }
    
    protected function writeHistory($order_id, $status)
    {
        History::create([
            'reference_table' => $this->ordersRepository->model(),
            'reference_id'    => $order_id,
            'actor_id'        => 1,
            'body'            => json_encode([
                'statuses' => [
                    $status->type => $status->id,
                ]
            ], JSON_UNESCAPED_UNICODE),
        ]);
    }
    
    protected function checkLastStatus($order, $last_step, $last_status)
    {
        // Нас интересуют заказы, где шаг перезвона больше или равен последнему шагу очереди
        if ($order['dial_step'] < $last_step) return false;
        
        // Пробуем установить статус
        if (!$this->setStatus($order['id'], $order['key'], $last_status, 'id')) return false;
        
        return $this->ordersRepository->find($order['id']);
    }
    
    protected function checkDSDay($order, $dsday)
    {
        // Нас интересуют заказы, которые созданы более 7 дней назад
        if ($order['create_date'] > \Carbon\Carbon::now()->subWeek()->format('Y-m-d H:i:s')) return false;
        
        // Пробуем установить статус
        if (!$this->setStatus($order['id'], $order['key'], $dsday, 'type')) return false;
        
        return $this->ordersRepository->find($order['id']);
    }
}



























