<?php
namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Organization;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class OrdersCallCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'OrdersCallCheck:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Проверка обзвона заказов';

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
        $this->checkCalls();
        $this->checkForCallCenter();
    }

    public function checkCalls()
    {
        $orders_without_status_1 = Order::whereDoesntHave('statuses', function ($query){
            $query->whereIn('type',[1]);
        })->with('statuses');

        $orders_without_calls = $orders_without_status_1->whereDoesntHave('calls')
            ->where('created_at', '<', Carbon::now()->subHours(12));

        $orders_by_org = $orders_without_calls->get()->groupBy('organization_id');

        $messages = "";

        $head_message = "Внимание!!! Данные заказы не прозваниваются больше 12-и часов.\n\n";
        $messages .= $head_message;

        // Ссылка на сайт c заявками
        $url = "https://crm.7282crmka.ru/orders/";

        foreach ($orders_by_org as $org){
            $org_id = $org->first()->organization_id;
            $org_name = Organization::find($org_id)->title;
            $message = "$org_name";
            foreach($org as $order){
                $phone = $order->phones[0];
                $key = $order->key;
                $phone_length = strlen($phone);
                $phone = substr($phone, 0, $phone_length-5).'*****';
                $link = $url.$key;
                $message.="\n$link, $phone";

            }
            $messages_length = strlen($messages);
            if($messages_length<4096){
                $messages .= $message."\n\n";
            }else{
                $messages = $message;
            }

        }

        $this->storeMessage($messages);
    }


    public function checkForCallCenter()
    {
        $orders_by_org = Order::where('created_at', '>', Carbon::now()->subDays(3)->toDateString())->with('geo')->with('statuses')->get()->groupBy('organization_id');
        $message = "Отчёт по проценту заказов в ожидании.\nИнструкция: goo.gl/pniqux.\n";

        foreach($orders_by_org as $orders){
            $org_name = $orders->first()->organization->title;
            $message = $message."\n".$org_name."\n";

            $orders_4 = $orders->where('created_at', '>=', Carbon::now()->subHours(4))
                ->groupBy('geo.name_en');
            if($hours_4 = $this->getMessageForDate($orders_4)){
                $hours_4_message = "*** За последние 4 часа: ".$hours_4."\n";
                $message .= $hours_4_message;
            }

            $orders_today = $orders->where('created_at', '>=', Carbon::today())
                ->groupBy('geo.name_en');
            if($today = $this->getMessageForDate($orders_today)){
                $yesterday_message = "*** За сегодня:".$today."\n";
                $message .= $yesterday_message;
            }

            $orders_yesterday = $orders->where('created_at', '>=',Carbon::yesterday())->where('created_at', '<', Carbon::today())
                ->groupBy('geo.name_en');
            if($yesterday = $this->getMessageForDate($orders_yesterday)){
                $yesterday_message = "*** За вчера: ".$yesterday."\n";
                $message .= $yesterday_message;
            }

            $orders_2_days = $orders->where('created_at', '>=', Carbon::now()->subDays(2)->toDateString())
                ->where('created_at', '<', Carbon::yesterday())->groupBy('geo.name_en');
            if($days_2 = $this->getMessageForDate($orders_2_days)){
                $days_2_message = "*** За позавчера: ".$days_2."\n";
                $message .= $days_2_message;
            }
        }
        $this->storeMessage($message);
    }

    public function getMessageForDate($orders)
    {
        $message = "";
        if($orders) {
            foreach ($orders as $orders_geo) {
                $geo_name = $orders_geo->first()->geo->code;
                $expecting_total = 0;
                $count_total = count($orders_geo);
                foreach ($orders_geo as $order) {
                    $type_1_parent_id = 0;
                    if (!$order->statuses) {
                        $expecting_total++;
                    }else {
                        foreach ($order->statuses as $status) {
                            if ($status->type == 1) {
                                $type_1_parent_id = $status->parent_id;
                                break;
                            }
                        }
                        if($type_1_parent_id == 0 || $type_1_parent_id == 18){
                            $expecting_total++;
                        }
                    }
                }
                $percent = round($expecting_total / $count_total * 100, 1);
                $temp = $geo_name . "-" . $percent . "%, ";
                $message .= $temp;
            }
        }
        return $message;
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
