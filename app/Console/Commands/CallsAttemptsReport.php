<?php
namespace App\Console\Commands;

use App\Models\Call;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Telegram\Bot\Laravel\Facades\Telegram;

class CallsAttemptsReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CallsAttemptsReport:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Отчёт по количеству попыток.';

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
        $this->getAttemptsInfo();
    }

    public function getAttemptsInfo(){

        $date = Carbon::today();
        $calls = DB::table('calls')
            ->join('orders as o', 'o.id', 'calls.order_id')
            ->where('calls.time', '>', $date)
            ->select('calls.order_id', 'o.country_code')
            ->get()->groupBy('country_code');

        $title = "Отчет по количеству попыток.\n";
        $message = '';
        foreach ($calls as $key=>$geo_calls){
            $orders = $geo_calls->pluck('order_id')->toArray();
            $calls_count = count($geo_calls);
            $orders_count = count(array_unique($orders));
            $calls_per_order = 0;
            if ($orders_count != 0){
                $calls_per_order = round($calls_count/$orders_count, 1);
            }
            $message .= $key. " - " .$calls_count. " попыток\n";
            $message .= $calls_per_order. " попыток на 1 заказ.\n\n";
        }

        if ($message == '')
            $message = "Ни одной попытки не обнаружено.";
        else{
            $message = $title.$message;
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