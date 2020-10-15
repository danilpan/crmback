<?php
namespace App\Console\Commands;

use App\Repositories\OrdersRepository;
use Carbon\Carbon;
use Elasticsearch\Client as ElasticClient;
use Illuminate\Console\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class ScriptsQualityReport extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ScriptsQualityReport:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Отчёт по качеству скриптов';

    /**
     * Create a new command instance.
     *
     * @return void
     */

    /**
     * @var ElasticClient
     */
    protected $ordersRepository;

    public function __construct(OrdersRepository $ordersRepository)
    {
        $this->ordersRepository = $ordersRepository;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->report_week_1($this->ordersRepository);
        $this->report_week_2($this->ordersRepository);
    }
    
    private function now()
    {
        // return Carbon::parse('2019-06-16 12:00:00'); // TEMP: Для удобства тестирования здесь можно глобально переопределить текущие дату и время
        return Carbon::now();
    }

    public function report_week_1(OrdersRepository $repository){
        $sub_7_days = $this->now()->subDays(7)->toDateString();
        
        $filter = ['created_at', '>', $sub_7_days];
        $request = $repository->makeRequest(0, 1000, $filter);
        $orders_sub_7 = $repository->dxSearchAll($request)
            ->groupBy(['geo.code', 'projects_title', 'project_goal_script'])
            ->toArray();

        foreach($orders_sub_7 as $key=>$geo_orders){
            $total_geo = 0;
            foreach ($geo_orders as $key1=>$project_orders){
                $total_count = 0;
                $total_geo += $total_count;
                foreach ($project_orders as $key2=>$script_orders){
                    $total_count += count($script_orders);
                    $orders_sub_7[$key][$key1][$key2]['total_count'] = count($script_orders);
                }
                $orders_sub_7[$key][$key1]['total_count'] = $total_count;
            }
            $orders_sub_7[$key] = collect($orders_sub_7[$key])->sortBy('total_count', SORT_REGULAR, true);
        }

        $message = "Отчет по качеству скриптов за неделю:\n";
        foreach($orders_sub_7 as $geo_key=>$geo_orders){
            $count = 0;
            $message .= "===$geo_key===\n";
            $approved = $trash = $expected = $rejected = 0;
            foreach($geo_orders as $project_key=>$project_orders){
                $message .= "- $project_key\n";
                foreach ($project_orders as $script_key => $script_orders){
                    $temp = $script_orders['total_count'];
                    if (is_array($script_orders)){
                        foreach ($script_orders as $script_order){
                            if (is_array($script_order)) {
                                switch ($script_order['current_1_group_status_id']){
                                    case 'status_group_17':
                                        $approved++;
                                        break;
                                    case 'status_group_18':
                                        $expected++;
                                        break;
                                    case 'status_group_19':
                                        $rejected++;
                                        break;
                                    case 'status_group_58':
                                        $trash++;
                                        break;
                                }
                                if ($temp > 0 && round($count/$temp, 1) > 0.7) {
                                    break;
                                }
                                $count += $temp;
                            }
                        }
                        $sum = $approved+$trash+$expected+$rejected;
                        if($sum > 0){
                            $temp = round(100*$approved/$sum, 1);
                            $message .= "- Скрипт $script_key - $temp %\n";
                        }
                    }
                }
            }
            $message .= "\n";
        }

        $this->storeMessage($message);
    }

    public function report_week_2(OrdersRepository $repository){
        $sub_7_days = $this->now()->subDays(7)->toDateString();
        $sub_14_days = $this->now()->subDays(14)->toDateString();

        $filter = [['created_at', '<', $sub_7_days], ['created_at', '>=', $sub_14_days]];
        $request = $repository->makeRequest(0, 1000, $filter);
        $orders_sub_14 = $repository->dxSearchAll($request)
            ->groupBy(['geo.code', 'projects_title', 'project_goal_script'])
            ->toArray();

        foreach($orders_sub_14 as $key=>$geo_orders){
            $total_geo = 0;
            foreach ($geo_orders as $key1=>$project_orders){
                $total_count = 0;
                $total_geo += $total_count;
                foreach ($project_orders as $key2=>$script_orders){
                    $total_count += count($script_orders);
                    $orders_sub_14[$key][$key1][$key2]['total_count'] = count($script_orders);
                }
                $orders_sub_14[$key][$key1]['total_count'] = $total_count;
            }
            $orders_sub_14[$key] = collect($orders_sub_14[$key])->sortBy('total_count', SORT_REGULAR, true);
        }

        $message = "Отчет по качеству скриптов за предыдущую неделю:\n";
        foreach($orders_sub_14 as $geo_key=>$geo_orders){
            $count = 0;
            $message .= "===$geo_key===\n";
            $approved = $trash = $expected = $rejected = 0;
            foreach($geo_orders as $project_key=>$project_orders){
                $message .= "- $project_key\n";
                foreach ($project_orders as $script_key => $script_orders){
                    $temp = $script_orders['total_count'];
                    if (is_array($script_orders)) {
                        foreach ($script_orders as $script_order){
                            if (is_array($script_order)) {
                                switch ($script_order['current_1_group_status_id']){
                                    case 'status_group_17':
                                        $approved++;
                                        break;
                                    case 'status_group_18':
                                        $expected++;
                                        break;
                                    case 'status_group_19':
                                        $rejected++;
                                        break;
                                    case 'status_group_58':
                                        $trash++;
                                        break;
                                }
                                if ($temp > 0 && round($count/$temp, 1) > 0.7) {
                                    break;
                                }
                                $count += $temp;
                            }
                        }
                        $sum = $approved+$trash+$expected+$rejected;
                        if($sum > 0){
                            $temp = round(100*$approved/$sum, 1);
                            $message .= "- Скрипт $script_key - $temp %\n\n";
                        }
                    }
                }
            }
        }

        $this->storeMessage($message);
    }

    public function storeMessage($data)
    {
        Telegram::sendMessage([
            'chat_id' => env('TELEGRAM_CHANNEL_ID', ''),
            'parse_mode' => 'HTML',
            'text' => $data
        ]);
    }

}