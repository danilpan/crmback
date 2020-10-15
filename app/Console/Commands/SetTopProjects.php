<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use DB;

use App\Models\Order;
use App\Models\ProjectGoal;
use App\Repositories\OrdersRepository;
use Carbon\Carbon;
use Elasticsearch\Client as ElasticClient;

class SetTopProjects extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SetTopProjects:set';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Определение топовых проектов';  

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        OrdersRepository $ordersRepository,        
        ElasticClient $elastic
    )
    {
        $this->ordersRepository = $ordersRepository;        
        $this->elastic = $elastic;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {        

        $start = microtime(true);
        $params = [];
        $params = [
            'index'     => $this->ordersRepository->getIndex(),
            'type'      => $this->ordersRepository->getType(),
            'size'      => 0,
            'from'      => null,
            'body' => [
                'sort'  => [],
                'query' => []
            ]
        ];

        $params['body']['query']['constant_score']['filter']['bool']['must'] = [];

        //У проекта категория кц = 5(Без категории)
        $filter['must']['bool']['must'][]['nested'] = [
                  "path" => "projects",
                  "query" => [
                     "term" => [
                        "projects.project_category_kc_id" => 5
                     ]
                  ],
                  "inner_hits" => []
        ];

        //Должна быть цель
        $filter['must']['bool']['must'][]['bool']['should'] = [
                [
                    'bool' => [
                        'must' => [
                            'exists' => [
                                'field' => 'project_goal.id'
                            ]
                        ]
                    ]
                ]
            ];    

        
        $filter['must']['bool']['must'][]['range']['created_at'] = ['lt'=>Carbon::now()->format('Y/m/d H:i:s')];
        $filter['must']['bool']['must'][]['range']['created_at'] = ['gt'=>Carbon::now()->subDays(4)->format('Y/m/d H:i:s')];//За последение 4 дян        

        $params['body']['aggs']['projects']['terms'] = ['field' => 'project_goal.id',"size" => 100000];  //Группировка по ID цели
        $params['body']['query']['constant_score']['filter']['bool']['must'] = $filter['must'];        


        /*$query['constant_score']['filter']['bool']['must'] = $filter['must'];

        $data = $this->ordersRepository->searchByParams(
            $query, 
            ['id'=>'asc'])->toArray();
        dd($data);*/

        $result  = $this->elastic->search($params);//результат               

        $temp_project_summ_top = 0;
        $project_goals_top = [];
        $project_name = "Список топовых офферов обновлен.\n\n".date('Y-m-d H:i:s')."\n\n";

        if($result['hits']['total']>0){
            $project_summ_leads = $result['hits']['total'];//Сумма заказов
            $iter = 1;
            foreach($result['aggregations']['projects']['buckets'] as $project_goal){                
                if($temp_project_summ_top<($project_summ_leads/2)){ //Нужны цели, составляющие половину суммы заказов
                    $project_goals_top[] = $project_goal['key'];             
                    $project_goal_data = ProjectGoal::find($project_goal['key']);
                    $project_name .= $iter.". ".($project_goal_data->project()->get()->first()->title.' '.$project_goal_data->geo()->get()->first()->code)."\n";
                    $temp_project_summ_top += $project_goal['doc_count'];
                    $iter++;
                } else {
                    break;
                }
            }
        }
        //dd($project_name);
        $was_top = ProjectGoal::where('top_t',true)->get();//были топовыми

        $was_top_ids = [];
        $still_top_ids = [];
        if($was_top->count() > 0){
            foreach ($was_top as $was_top_p) {
                if(!in_array($was_top_p->id, $project_goals_top)){ //перестали быть топовыми
                    $was_top_ids[] = $was_top_p->id;
                    $was_top_p->top_t = false;
                    $was_top_p->save();
                }else{
                    $still_top_ids[] = $was_top_p->id; //остались топовыми
                }
            }                        
        }

        $new_top = ProjectGoal::whereIn('id', $project_goals_top)->get();//топовые
        $new_top_ids = [];
        if($new_top->count() > 0){
            foreach ($new_top as $new_top_p) {
                if(!in_array($new_top_p->id, $still_top_ids)){//стали топовыми, раньше не были
                    $new_top_ids[] = $new_top_p->id;
                    $new_top_p->top_t = true;
                    $new_top_p->save();
                }
            }                        
        }

        //обновление заказов, только по измененным целям
        $orders = Order::whereIn('project_goal_id',array_merge($new_top_ids,$was_top_ids))->get();       

        $count_orders = $orders->count();

        $this->ordersRepository->reindexByData($orders);

        $project_name .= "\n";
        $project_name .= "Заказов обновлено: ".$count_orders."\n";
        $project_name .= "Время выполнения скрипта: ".(microtime(true) - $start);

        //Отправка в телеграм
        file_get_contents ( 'https://api.telegram.org/bot506628398:AAEDwXyvPG0rbKva5p5zE3XDT4dDjrCTAU0/sendMessage?text='.urlencode($project_name).'&chat_id=-1001241526671&parse_mode=html');        

        dd($project_name);

    }
    
}
