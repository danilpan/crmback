<?php
namespace App\Console\Commands;

use App\Models\AtsQueue;
use App\Models\QueueStateHistory;
use Illuminate\Console\Command;

class LogQueueStateHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'LogQueueStateHistory:log';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Логирование состояния очередей';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $queues = AtsQueue::where('type', 'auto')->orWhere('type', 'in')->get();
        foreach ($queues as $queue){
            $item = [];
            $item['queue_id'] = $queue->id;
            $item['main'] = $item['online'] = $item['speak'] = $item['option_in_calls'] = 0;
            if($operators = $queue->callerIdsOper){
                foreach ($operators as $operator){
                    if($status = $operator->status->last()){
                        $item['main']++;
                        if($operator->atsUser->option_in_call == true){
                            if(mb_strtolower($status->status->name_en) == 'online' || mb_strtolower($status->status->name_en) == 'ringing'){
                                $item['online']++;
                            }
                            if(mb_strtolower($status->status->name_en) == 'speak'){
                                $item['speak']++;
                            }
                        }else{
                            $item['option_in_calls']++;
                        }
                    }
                }
            }

            QueueStateHistory::create($item);
        }

        $this->info('Логирование очереди прошло успешно!');
    }
}