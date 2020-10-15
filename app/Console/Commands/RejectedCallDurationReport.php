<?php
namespace App\Console\Commands;

use App\Models\Organization;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Services\StatusesService;
use DB;

class RejectedCallDurationReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'RejectedCallDurationReport:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Отчёт продолжительности отклоненного разговора';
    
    protected $statusesService;
    
    public function __construct(StatusesService $statusesService)
    {
        parent::__construct();
        $this->statusesService = $statusesService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->buildReport();
    }
    
    private function getTime($duration)
    {
        $hours = floor($duration / 3600);
        $mins = floor($duration / 60 % 60);
        $secs = floor($duration % 60);
        if ($hours > 0) {
            $timeFormat = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
        } elseif ($mins > 0) {
            $timeFormat = sprintf('%02d:%02d', $mins, $secs);
        } else {
            $timeFormat = sprintf('%02d', $secs);
        }
        
        return $timeFormat;
    }

    public function buildReport()
    {
        $message = "Отчёт продолжительности отклоненного разговора:\n";
        $kc_child = Organization::where('parent_id', env('MAIN_KC_ID', 3))->get();
        $kc_main_ids = $kc_child->pluck('id')->toArray(); // Айди головных коллцентров
        $kc_child_ids = []; // Айди отделов группированные по головным
        $kc_child_ids_plain = []; // Айди отделов списком для whereIn
        
        foreach ($kc_child as $child){
            $kc_child_ids[$child->id] = $child->getChildIds();
            $kc_child_ids_plain = array_merge($kc_child_ids_plain, $kc_child_ids[$child->id]);
        }

        $rejected_statuses_ids = $this->statusesService->getChildsById(19)->pluck('id')->toArray();

        // Звонки группированные по КЦ и заказам
        $data = DB::table('orders as o')
            ->whereNotNull('o.manager_id')
            ->where('o.created_at', '>', Carbon::now()->subMonth(1)->toDateString())
            ->join('order_status as os', function ($jin) use ($rejected_statuses_ids) {
                $jin->on('os.order_id', '=', 'o.id')
                    ->whereIn('os.status_id', $rejected_statuses_ids);
            })
            ->join('calls as c', 'c.order_id', 'o.id')
            ->join('users as u', function ($jin) use ($kc_child_ids_plain) {
                $jin->on('u.id', '=', 'o.manager_id')
                    ->whereIn('u.organization_id', $kc_child_ids_plain);
            })
            ->select('c.duration_time', 'u.organization_id as kc', 'o.id as order_id')
            ->get()
            ->groupBy(['kc', 'order_id']);
        
        // Список головных КЦ с наименованиями
        $kc_list = DB::table('organizations as org')
            ->whereIn('org.id', $kc_main_ids)
            ->select('id', 'title')
            ->get();
        
        $avg_durations_by_kc = []; // Средняя продолжительность звонков по отделам КЦ
        foreach ($data as $kc_id => $kc_orders) {
            $duration = 0; // Длительность всех звонков по отделу КЦ
            foreach ($kc_orders as $order_id => $order_calls) {
                foreach ($order_calls as $call) {
                    $duration += $call->duration_time;
                }
            }
            $kc_avg_duration = $duration / count($kc_orders);
            
            // Группируем длительности отделов по КЦ
            foreach ($kc_child_ids as $main_kc_id => $childs_kc) {
                if (in_array($kc_id, $childs_kc)) {
                    $avg_durations_by_kc[$main_kc_id][$kc_id] = $kc_avg_duration;
                }
            }
        }
        
        // Формируем сообщение
        foreach ($avg_durations_by_kc as $main_kc_id => $avg_durations_by_childs_kc) {
            $duration_by_main = 0;
            foreach ($avg_durations_by_childs_kc as $duration) {
                $duration_by_main += $duration;
            }
            $kc_title = $kc_list->where('id', $main_kc_id)->first()->title;
            $kc_duration = $duration_by_main / count($avg_durations_by_childs_kc);
            
            $message .= "- ".$kc_title." - ".$this->getTime($kc_duration);
            $message .= "\n";
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
