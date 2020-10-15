<?php
namespace App\Repositories;

use App\Models\AtsStatus;
use App\Models\AtsUser;
use Bosnadev\Repositories\Contracts\RepositoryInterface;
use Bosnadev\Repositories\Eloquent\Repository as BaseRepository;
use App\Models\ModelIterface as Model;
use Carbon\Carbon;
use Elasticsearch\Client as ElasticClient;
use Illuminate\Container\Container as App;
use Illuminate\Support\Collection;
use App\Collections\SearchResultCollection;
use App\Collections\SuggestResultCollection;
use Exception;
use DB;

abstract class Repository extends BaseRepository implements  RepositoryInterface
{
    protected $elastic;

    public function __construct(App $app, Collection $collection, ElasticClient $elastic)
    {
        $this->elastic  = $elastic;
        parent::__construct($app, $collection);
    }

    public function update(array $data, $id, $attribute = "id")
    {
        $model  = $this->find($id);
        if($model) {
            $model->fill($data);
            $model->save();
        }

        return $model;
    }

    public function updateBy(array $data, $id, $attribute = "id")
    {
        $model  = $this->findBy($attribute, $id);
        if($model) {
            $model->fill($data);
            try{
                $model->save();
            }
            catch(Exception $e){
                dd($e);
            }
        }

        return $model;
    }

    private function getField($item)
    {
    	return $item['field'];
    }

    public function search($page = 1, $perPage = 20, $sortKey = null, $sortDirection = null, $filters = null, $queryString = null)
    {
	
        $queryString = mb_strtolower($queryString,'UTF-8');
        $query  = [];
        if($queryString) {
    //            array_set($query, 'bool.must.multi_match', [
    //                'query'     => $queryString,
    //                'fields'    => $this->getQueryFields()
    //            ]);
        
	    $queryFields    = [];
	    if(count(preg_split('/\s+/', $queryString)) > 1){
		array_set($query, 'multi_match', [
			'query'     => $queryString,
			'type'	    => 'phrase',
                	'fields'    => array_map(array($this,'getField'), $this->getQueryFields())
            	]);
 
	    }else	    
            foreach ($this->getQueryFields() as $queryField) {
               $f = null;
               switch ($queryField['type']) {
                   case 'terms':
                       $f   = [
                           'terms'  => [
                               $queryField['field'] => [$queryString]
			   ]
                       ];
                       break;
                   case 'wildcard' :
                       $f   = [
                           'wildcard'  => [
                               $queryField['field'] => '*' . str_replace('*', '', $queryString) . '*'
                           ]
                       ];

                       break;
               }

               if($f) {
                   array_push($queryFields, $f);
               }

            array_set($query, 'bool.should', $queryFields);
  
        }
        

//            array_set($query, 'bool.must.multi_match.type', 'cross_fields');
//            array_set($query, 'bool.must.multi_match.operator', 'and');
        }
        else {
            array_set($query, 'bool.must.match_all', new \stdClass());
        }


        if(!empty($filters)) {
            $queryFilters   = [];
            $queryExclude   = [];

            foreach ($filters as $fKey => $fValue) {
                $queryFilter    = null;
                if(isset($fValue['terms'])) {
                    $queryFilter    = [
                        'terms'  => [
                            $fKey   => (array)$fValue['terms']
                        ]
                    ];
                }
                elseif(isset($fValue['from']) || isset($fValue['to'])) {
                    $range  = [];
                    if(isset($fValue['from'])) {
                        $range['gte']   = $fValue['from'];
                    }

                    if(isset($fValue['to'])) {
                        $range['lte']   = $fValue['to'];
                    }

                    $queryFilter    = [
                        'range' => [
                            $fKey   => $range
                        ]
                    ];
                }
                elseif(isset($fValue['prefix']) || isset($fValue['postfix']) || isset($fValue['substring'])) {

                    $value = '';

                    if(isset($fValue['prefix'])) {
                        $value = $fValue['prefix'] . '*';
                    }
                    else if($fValue['postfix']) {
                        $value = '*'. $fValue['postfix'];
                    }
                    else if(isset($fValue['substring'])) {
                        $value = '*'. $fValue['substring'] . '*';
                    }

                    $queryFilter    = [
                        'wildcard'  => [
                            $fKey   => $value
                        ]
                    ];

                }
                else {
//                    throw new Exception('unknown filter type for field ' . $fKey);
                }

                if($queryFilter) {
                    $exclude    = array_get($fValue, 'exclude', false);
                    if($exclude) {
                        $queryExclude[] = $queryFilter;
                    }
                    else {
                        $queryFilters[] = $queryFilter;
                    }
                }

//                $queryFilters[] = [
//                    'terms'  => [
//                        $fKey   => (array)$fValue
//                    ]
//                ];
            }


            // array_set($query, 'bool.filter.bool.should',    $queryFilters);
            array_set($query, 'bool.filter',    $queryFilters);
            array_set($query, 'bool.must_not',  $queryExclude);
        }

        $querySort  = [];
        if($sortKey) {
            $querySort[]    = [
                $sortKey    => $sortDirection ?? 'asc'
            ];
        }


//        $highlightFields    = array_map($this->getQueryFields(), function ($f) {
//
//        });
        $highlightFields    = [];
        foreach ($this->getQueryFields() as $f) {
            $highlightFields[$f['field']]    = new \stdClass();
        }

        $params = [
            'index'     => $this->getIndex(),
            'type'      => $this->getType(),
            'size'      => $perPage,
            'from'      => ($page - 1) * $perPage,
            'body' => [
                'query'     => $query,
                'sort'      => $querySort,
                'highlight' => [
                    'pre_tags'  => '<em>',
                    'post_tags' => '</em>',
                    'fields'    => $highlightFields
                ]

            ]
        ];

//        "highlight"  => [
//        "pre_tags" : ["<em>"],
//        "post_tags" : ["</em>"],
//        "fields"  => [
//            "key"							 => [},
//            "import_id"						 => [},
//            "import_webmaster_id"			 => [},
//            "import_webmaster_transit_id"	 => [},
//            "request_hash"					 => [},
//            "api_key"						 => [},
//            "phones"						 => [},
//            "address"						 => [}
//        }
//    }

        if(request()->get('elastic_debug')) {
            return $params;
        }

        $client     = $this->getElasticClient();
        $result     = $client->search($params);
        $collection = $this->hidrateSearchResult($result);

        return $collection;
    }

    public function searchByParams($query, $sort, int $page = 1, int $size = 20, $isNeedHighlight = true ){

        $highlightFields    = [];

        if($isNeedHighlight)
            foreach ($this->getQueryFields() as $f) {
                $highlightFields[$f['field']]    = new \stdClass();
            }

        $params = [
            'index'     => $this->getIndex(),
            'type'      => $this->getType(),
            'size'      => $size,
            'from'      => ($page - 1) * $size,
            'body' => [
                'query'     => $query,
                'sort'      => $sort,
                'highlight' => [
                    'pre_tags'  => '<em>',
                    'post_tags' => '</em>',
                    'fields'    => $highlightFields
                ]

            ]
        ];

        $client     = $this->getElasticClient();
        $result     = $client->search($params);
        $collection = $this->hidrateSearchResult($result);

        return $collection;

    }

    public function dxGroupedSearch($data)
    {
        array_set($query, 'bool.must.match_all', new \stdClass());
        $sort  = [];
       
        if(isset($data['sort'])) {
            $sort = $this->dxSortBuild(json_decode($data['sort']));
        }
        
        if(isset($data['filter'])) {
            $query = null;
            $query['constant_score']['filter']['bool']['must'] = $this->dxQueryBuild(json_decode($data['filter']));
        }
        
        $params = [
            'index'     => $this->getIndex(),
            'type'      => $this->getType(),
            'size'      => $data['take'],
            'from'      => $data['skip'],
            'body' => [
                'sort'  => $sort,
                'query' => $query
            ]
        ];
        
        $group = false;
        if(isset($data['group'])){
            $group = true;
            $params['size']=0;
            $params['body']['aggs'] = [
                "group" => [
                    "terms" => [
                        "field" => json_decode($data['group'])[0]->selector
                    ]
                ]
            ];
        }

        $key = null;
        if(isset($data['group'])){
            $key=json_decode($data['group'])[0]->selector;
        }


        $client     = $this->getElasticClient();
        $result     = $client->search($params);
    
        if($group) 
            $result = $this->dxBuildGroupResult($result);

        $collection = $this->hidrateSearchResult($result, $key);

        return $collection;
    }
    
    
    public function makeRequest($skip, $take, $filter) {
        $request = [];
        $request['skip'] = $skip;
        $request['take'] = $take;
        $request['filter'] = json_encode($filter);
        return $request;
    }
    
    public function dxSearchAll($data)
    {
        $data['skip'] = 0;
        
        if (!isset($data['take'])) {
            $data['take'] = 500;
        }
        
        $result = $this->dxSearch($data);
        $collection = $result;
        $total = $result->getTotal();
        
        for ($i=1; $i < ceil($total / $data['take']); $i++) {
            $data['skip'] = $i * $data['take'];
            $result = $this->dxSearch($data);
            $collection = $collection->merge($result);
        }
        
        return $collection;
    }

    public function dxSearch($data)
    {
        array_set($query, 'bool.must.match_all', new \stdClass());
        $sort  = [];
       
        if(isset($data['sort'])) {
            $sort = $this->dxSortBuild(json_decode($data['sort']));
        }
        

        if(isset($data['filter'])) {
            $query = null;
            $query['constant_score']['filter']['bool']['must'] = $this->dxQueryBuild(json_decode($data['filter']));
        }

        $params = [
            'index'     => $this->getIndex(),
            'type'      => $this->getType(),
            'size'      => $data['take'],
            'from'      => $data['skip'],
            'body' => [
                'sort'  => $sort,
                'query' => $query
            ]
        ];
        if(isset($data['source'])){
            $params['_source'] = json_decode($data['source']);
        }


        if(isset($data['report'])){
            $params['size']=0;

            $params['body']['aggs'] = $this->dxBuildParams($data['report']);

            if(explode("%",$data['section'])[0] == "date"){
                $params['body']['aggs']['group']['date_histogram'] = [
                    "field"=>explode("%",$data['section'])[1],
                    "interval"=>"day"
                ];
            }else{
                $params['body']['aggs']['group']['terms']['field'] = $data['section'];
                $params['body']['aggs']['group']['terms']['size'] = 10000;
            }

            
        }

/*        if(isset($data['report'])){
            $params['size']=0;

            if(explode("%",$data['section'])[0] == "date"){
                $params['body']['aggs']['group']['date_histogram'] = [
                    "field"=>explode("%",$data['section'])[1],
                    "interval"=>"day"
                ];
                $params['body']['aggs'] = $this->dxBuildParams($data['report']);
            }else{
                $params['body']['aggs'] = $this->dxBuildParams($data['report']);
                $params['body']['aggs']['group']['terms']['field'] = $data['section'];
                $params['body']['aggs']['group']['terms']['size'] = 10000;
                if($data['section1']){
                    $params['body']['aggs']['group']['aggs'] = $this->dxBuildParams($data['report']);
                    $params['body']['aggs']['group']['aggs']['group']['terms']['field'] = $data['section1'];
                    $params['body']['aggs']['group']['aggs']['group']['terms']['size'] = 10000;
                    if($data['section2']){
                        $params['body']['aggs']['group']['aggs']['group']['aggs'] = $this->dxBuildParams($data['report']);
                        $params['body']['aggs']['group']['aggs']['group']['aggs']['group']['terms']['field'] = $data['section2'];
                        $params['body']['aggs']['group']['aggs']['group']['aggs']['group']['terms']['size'] = 10000;
                            if($data['section3']){
                                $params['body']['aggs']['group']['aggs']['group']['aggs']['group']['aggs']= $this->dxBuildParams($data['report']);
                                $params['body']['aggs']['group']['aggs']['group']['aggs']['group']['aggs']['group']['terms']['field'] = $data['section3'];
                                $params['body']['aggs']['group']['aggs']['group']['aggs']['group']['aggs']['group']['terms']['size'] = 10000;
                        }
                    }
                }
            }
        }*/

        $key = null;
        if(isset($data['group'])){
            $key=json_decode($data['group'])[0]->selector;
        }

        if($key && isset(json_decode($data['group'])[0]->groupInterval)){
            $params['body']['aggs'] = [
                "dt" => [
                    "date_histogram" => [
                        "field" => $key,
                        "interval" => "day"
                    ],
                    "aggs" => [
                        "id" => [
                            "terms" =>  [
                                "field" => "unique_identifier"
                            ]
                        ]
                    ]
                ]
            ];
        }

        
        $client     = $this->getElasticClient();
        $result     = $client->search($params);
        if($key && isset(json_decode($data['group'])[0]->groupInterval)){
            $result = $this->dxDateFilterBuild($result);
            $key = null;
        }

        if(isset($data['report'])) 
            $result = $this->dxBuildReport($data['report'], $result);

        $collection = $this->hidrateSearchResult($result, $key);

        return $collection;
    }
    private function dxBuildParams($report){
        $result = [];
        switch($report){
            case "accounting_report":
            case "frod_report":
                $result = [
                    "group" => [
                        "aggs" => [
                            "status" => [
                                "terms" =>  [
                                    "field" => "statuses.id"
                                ]
                            ]
                        ]
                    ]
                ];
                break;
            case "logistic_report":
            case "common_report":
                $result = [
                    "group" => [
                        "aggs" => [
                            "status" => [
                                "terms" =>  [
                                    "field" => "statuses.id"
                                ],
                                "aggs" => [
                                    "summa" => [
                                        "sum" => [
                                            "field"=>"sales.price"
                                        ]
                                    ]
                                ]
                            ],
                            "upsale" =>[
                                "terms" =>  [
                                    "field" =>  "sales.upsale"
                                ]
                            ],
                            "transit" =>[
                                "terms" =>  [
                                    "field" =>  "goods_in_transit"
                                ]
                            ]
                        ]
                    ]
                ];
                break;
            case "call_center_report":
                $result = [
                    "group" => [
                        "aggs" => [
                            "sales" => [
                                "terms" =>  [
                                    "field" => "current_1_group_status_id"
                                ]
                            ],
                            "operator_id"=>[
                                "terms" => [
                                    "field" => "operator.id"
                                ]
                            ],
                            "group_by_categories_1" => [
                                "terms" =>  [
                                    "field" => "projects.project_category_kc_id"
                                ],
                                "aggs" => [
                                    "group_by_statuses" =>[
                                        "terms" =>[
                                            "field" => "current_1_group_status_id"
                                        ]
                                    ]
                                ]
                            ],
                            "group_by_categories_2" => [
                                "terms" =>  [
                                    "field" => "projects.project_category_kc_id"
                                ],
                                "aggs" => [
                                    "group_by_upsales_user_id" =>[
                                        "terms" =>[
                                            "field" => "sales.upsale_user_id"
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    "status" => [
                        "terms" => [
                            "field" => "statuses.id"
                        ],
                        "aggs" => [
                            "upsale_operators" => [
                               "terms" => [
                                    "field" => "statuses.upsale_operator",
                                    "size" => 100000
                                ]
                            ]
                        ]
                    ]
                ];
                break;
            case "sales_report":
                $result = [
                    "group" => [
                        "aggs" => [
                            "group" => [
                                "terms" =>  [
                                    "field" => "current_1_group_status_id",
                                    "size" => 100000
                                ]
                            ],
                            "approved" => [
                                "terms" =>  [
                                    "field" => "approved",
                                    "size" => 100000
                                ]
                            ],
                            "rejected" => [
                                "terms" =>  [
                                    "field" => "rejected",
                                    "size" => 100000
                                ]
                            ],
                            "status" => [
                                "terms" =>  [
                                    "field" => "statuses.id",
                                    "size" => 100000
                                ],
                                "aggs" => [
                                    "price_avg" =>[
                                        "avg" =>  [
                                            "field" =>  "quantity_price_sum"
                                        ]
                                    ],
                                    "upsale_1_sum" =>[
                                        "sum" =>  [
                                            "field" =>  "upsale_1_sum"
                                        ]
                                    ],
                                    "upsale_2_sum" =>[
                                        "sum" =>  [
                                            "field" =>  "upsale_2_sum"
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    "status" => [
                        "terms" => [
                            "field" => "statuses.id"
                        ],
                        "aggs" => [
                            "upsale_operators" => [
                               "terms" => [
                                    "field" => "statuses.upsale_operator",
                                    "size" => 100000
                                ]
                            ]
                        ]
                    ]
                ];
                break;
            case "sales_analytics_report":
                $result = [
                    "group" => [
                        "aggs" => [
                            "status_1c_3" => [
                                "terms" =>  [
                                    "field" => "status_1c_3",
                                    "size"  => 100000,
                                ],
                                "aggs" => [
                                    "logistic_status" => [
                                        "terms" => [
                                            "field" => "status_group_3_title",
                                            "size"  => 100000,
                                        ],
                                        "aggs" => [
                                            "date_1c" => [
                                                "terms" => [
                                                    "field" => "created_at",
                                                    "size"  => 100000,
                                                ]
                                            ],
                                        ]
                                    ],
                                    "status" => [
                                        "terms" => [
                                            "field" => "statuses.id",
                                            "size"  => 100000,
                                        ],
                                        "aggs" => [
                                            "date_1c" => [
                                                "terms" => [
                                                    "field" => "created_at",
                                                    "size"  => 100000,
                                                ]
                                            ],
                                        ]
                                    ],
                                    "delivery_price" => [
                                        "terms" => [
                                            "field" => "delivery_price",
                                            "size"  => 100000,
                                        ]
                                    ],
                                    "date_1c" => [
                                        "terms" => [
                                            "field" => "created_at",
                                            "size"  => 100000,
                                        ],
                                        "aggs" => [
                                            "status" => [
                                                "terms" => [
                                                    "field" => "statuses.id",
                                                    "size" => 100000
                                                ]
                                            ]
                                        ]
                                    ],
                                    "project_goal_price" => [
                                        "terms" => [
                                            "field" => "project_goal_price",
                                            "size"  => 100000
                                        ],
                                        "aggs" => [
                                            "quantity_price" => [
                                                "terms" => [
                                                    "field" => "quantity_price_sum",
                                                    "size" => 100000
                                                ]
                                            ]
                                        ]
                                    ],
                                    "price_avg" => [
                                        "avg" => [
                                            "field" => "quantity_price_sum"
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ];
                break;
        }
        return $result;
    }

    private function dxBuildReport($report, $result)
    {   
        switch($report){
            case "logistic_report":
                $result= $this->dxBuildLogisticReport($result);
                break;
            case "accounting_report":
                $result= $this->dxBuildAccountingReport($result);
                break;
            case "frod_report":
                $result= $this->dxBuildFrodReport($result);
                break;
            case "common_report":
                $result= $this->dxBuildCommonReport($result);
                break;
            case "sales_report":
                $result=$this->dxBuildSalesReport($result);
                break;
            case "call_center_report":
                $result=$this->dxBuildCallCenterWagesReport($result);
                break;
            case "sales_analytics_report":
                $result=$this->dxBuildSalesAnalyticsReport($result);
        }
        return $result;
    }

    private function dxBuildGroupResult($result){
        $buckets = $result['aggregations']['group']['buckets'];

        foreach($buckets as $b){
            $item = [
                'key' => $b['key'],
                'summary' => round($b['doc_count']/$result['hits']['total']*100,1)
            ];
            $result['hits']['hits'][]['_source'] = $item;
        }

        $result['hits']['total'] = count($buckets);

        return $result;
    }

    public function dxBuildCallCenterWagesReport($result)
    {
        /*********************************
        *    Расчет апсейлов операторов. *
        **********************************/
        $upsale_operators = $result['aggregations']['status']['buckets'];//[0]['upsale_operators']['buckets'];

        $upsale_operators_value = $this->operatorUpsales($upsale_operators, true);
        
        $pckc = DB::table('project_category_kc')->select('id', 'title')->get();
        foreach ($pckc as $key => $value) {
            switch (trim($value->title)) {
                case 'Рублевые':
                    define("KC_RUBL", (string)$value->id);
                    break;
                case 'Нутра':
                    define("KC_NUTRA", (string)$value->id);
                    break;
                case 'Комплекты':
                    define("KC_COMPLECT", (string)$value->id);
                    break;
                case 'Полурублевые':
                    define("KC_POLRUBL", (string)$value->id);
                    break;
                case 'Без категории':
                    define("KC_WITHOUT", (string)$value->id);
                    break;
            }
        }

        // dd($upsale_operators_value);

        // $k5_upsales = [];
        // $k4_upsales = [];
        // $k3_upsales = [];
        // $k2_upsales = [];
        // $k1_upsales = [];

        // foreach ($buckets as $key => $value) {
        //     if($value['key'] == "5")
        //         $k5_upsales = $this->operatorUpsales($value['status']['buckets']);
        //     if($value['key'] == "4")   
        //         $k4_upsales = $this->operatorUpsales($value['status']['buckets']); 
        //     if($value['key'] == "3")   
        //         $k4_upsales = $this->operatorUpsales($value['status']['buckets']);
        //     if($value['key'] == "2")       
        //         $k4_upsales = $this->operatorUpsales($value['status']['buckets']);
        //     if($value['key'] == "1")       
        //         $k4_upsales = $this->operatorUpsales($value['status']['buckets']);
        // }

        $params = request()->get('filter');
        $query = $this->dxQueryBuild(json_decode($params));
        $gte = $lte = "";
        $online_status_id = AtsStatus::where('name_en', 'Online')->first()->id;             // айди статуса "Онлайн"
        $ringing_status_id = AtsStatus::where('name_en', 'Ringing')->first()->id;           // айди статуса "Не берет трубку"
        $speak_status_id = AtsStatus::where('name_en', 'Speak')->first()->id;               // айди статуса "В разговоре"
        $online_statuses_ids = [$online_status_id, $ringing_status_id, $speak_status_id];   // массив айдишек рабочего времени

        foreach($query['bool']['must'] as $item){
            if(isset($item[0])) {
                if ((array_keys($item[0])[0]) == "range") {
                    $date_key = key($item[0]['range']);
                    if (array_keys($item[0]['range'][$date_key])[0] == "gte") {
                        $gte = Carbon::parse($item[0]['range'][$date_key]['gte'])->toDateString();
                    }
                    else {                                                                                // временные рамки из урла
                        $lte = Carbon::parse($item[0]['range'][$date_key]['lte'])->toDateString();
                    }
                }
            }
        }

        $buckets = $result['aggregations']['group']['buckets'];
        foreach($buckets as $b){                                                            // итерация по каждому оператору
            if($b['doc_count']==0)
                continue;
            $item = [
                'key' => $b['key'],
                'operator.title' => $b['key']
            ];


            $operator_ids = $b['operator_id']['buckets'];                                   // все id оператора с таким именем (если несколько профилей)
            $operator_id = $b['operator_id']['buckets'][0]['key'];                          // id оператора
            $atsUsers = AtsUser::where('user_id', $operator_id)->pluck('id')->toArray();    // АТС профиль оператора
            $callerIds = [];                                                                // номера звонилок
            $day_minutes =  $night_minutes = 0;                                             // общее дневное время && общее ночное время
            $free_minutes = $speak_minutes = 0;                                             // был свободен && был в разговоре
            $workload_percent = 0;                                                          // % загруженности

            if (count($atsUsers) > 0){
                
                $callerIds = DB::table('sip_caller_ids as sci')
                    ->whereIn('sci.ats_user_id', $atsUsers)->pluck('caller_id')->toArray(); 

                $statuses = DB::table('user_status_logs')->
                            whereBetween('created_at', [$gte." 00:00:00", $lte." 23:59:59"])->
                            whereIn("ats_user_id", $atsUsers)->
                            get();

                foreach ($statuses as $key => $value){
                    if (in_array($value->status_id, $online_statuses_ids) && isset($statuses[$key + 1])) {

                        $from = Carbon::parse($value->created_at);                               // Начало отсчета статуса
                        $to = Carbon::parse($statuses[$key + 1]->created_at );                  // Конец отсчета статуса
                        $diff = ceil($from->diffInSeconds($to)/60);                            // Длительность статуса в минутах
                        $morning = Carbon::parse($from)->toDateString() . " 09:00:00";
                        $night = Carbon::parse($from)->toDateString() . " 21:00:00";
                        $next_morning = Carbon::parse($from)->addDay()->toDateString() . " 09:00:00";
                        if($from >= $morning && $from < $night){
                            $day_minutes += $diff;
                        }
                        elseif($from >= $night && $from <= $next_morning){
                            $night_minutes += $diff;
                        }
                        if($value->status_id == $online_status_id || $value->status_id == $ringing_status_id){
                            $free_minutes += $diff;
                        }
                        if($value->status_id == $speak_status_id){
                            $speak_minutes += $diff;
                        }
                    }
                }

                if (($free_minutes+$speak_minutes) > 0){
                    $workload_percent = round($speak_minutes/($speak_minutes+$free_minutes)*100, 1);
                }
            }

            $item['C1'] = 65;
            $item['C2'] = 200;
            $item['C3'] = 100;
            $item['UP1'] = 75;
            $item['UP2'] = 250;
            $item['UP3'] = 105;
            $item['T1'] = 4;
            $item['T2'] = 5;
            $item['C1_quantity'] = $item['C2_quantity'] = $item['C3_quantity'] = 0;
            $item['UP1_quantity'] = $item['UP2_quantity'] = $item['UP3_quantity'] = 0;
            $item['confirmed_sales'] = $item['confirmed_upsales'] = 0;

            $item['T1_quantity'] = $day_minutes;
            $item['T2_quantity'] = $night_minutes;
            $item['free_time']   = $free_minutes;
            $item['speak_time']  = $speak_minutes;
            $item['number'] = $callerIds;
            $item['workload_percent'] = $workload_percent;

            if(isset($b['key_as_string'])){
                $date = date_create_from_format('Y/m/d H:i:s', $b['key_as_string']);
                $item['key'] =$date->format('d.m.Y');
            }

            $total_c1 = $total_c2 = $total_c3 = 0;
            $total_up1 = $total_up2 = $total_up3 = 0;

            foreach ($b['sales']['buckets'] as $bs) {
                if ($bs['key'] == 'status_group_17')
                    $item['confirmed_sales'] = $bs['doc_count'];
            }

            foreach($b['group_by_categories_1']['buckets'] as $bi){
                if($bi['key'] == KC_WITHOUT || $bi['key'] == KC_NUTRA) {                //  Без категории и Нутра
                    foreach ($bi['group_by_statuses']['buckets'] as $bs) {
                        if ($bs['key'] == 'status_group_17')
                            $total_c1 += $bs['doc_count'];
                    }
                    $item['C1_quantity'] = $total_c1;
                }
                if($bi['key'] == KC_RUBL) {                                             //  Рублевые
                    foreach ($bi['group_by_statuses']['buckets'] as $bs) {
                        if ($bs['key'] == 'status_group_17')
                            $total_c2 += $bs['doc_count'];
                    }
                    $item['C2_quantity'] = $total_c2;
                }
                if($bi['key'] == KC_POLRUBL || $bi['key'] == KC_COMPLECT) {             //  Полурублевые и Комплекты
                    foreach ($bi['group_by_statuses']['buckets'] as $bs) {
                        if ($bs['key'] == 'status_group_17')
                            $total_c3 += $bs['doc_count'];
                    }
                    $item['C3_quantity'] = $total_c3;
                }
            }


            foreach ($upsale_operators_value as $key=>$u_operator) {
               if($item['key'] == $key){
                    

                    //  Без категории и Нутра
                    if(isset($upsale_operators_value[$key][KC_WITHOUT]))
                        $item['UP1_quantity'] += $upsale_operators_value[$key][KC_WITHOUT]['upsale_1_sum']+$upsale_operators_value[$key][KC_WITHOUT]['upsale_2_sum'];
                    if(isset($upsale_operators_value[$key][KC_NUTRA]))
                        $item['UP1_quantity'] += $upsale_operators_value[$key][KC_NUTRA]['upsale_1_sum']+$upsale_operators_value[$key][KC_NUTRA]['upsale_2_sum'];

                    //  Рублевые
                    if(isset($upsale_operators_value[$key][KC_RUBL]))
                        $item['UP2_quantity'] += $upsale_operators_value[$key][KC_RUBL]['upsale_1_sum']+$upsale_operators_value[$key][KC_RUBL]['upsale_2_sum'];

                    //  Полурублевые и Комплекты
                    if(isset($upsale_operators_value[$key][KC_POLRUBL]))
                        $item['UP3_quantity'] += $upsale_operators_value[$key][KC_POLRUBL]['upsale_1_sum']+$upsale_operators_value[$key][KC_POLRUBL]['upsale_2_sum'];
                    if(isset($upsale_operators_value[$key][KC_COMPLECT]))
                        $item['UP3_quantity'] += $upsale_operators_value[$key][KC_COMPLECT]['upsale_1_sum']+$upsale_operators_value[$key][KC_COMPLECT]['upsale_2_sum'];

               }
            }

/*            foreach ($k2_upsales as $key=>$u_operator) {
               if($item['key'] == $key){
                    $item['UP1_quantity'] += $u_operator['upsale_1_sum']+$u_operator['upsale_2_sum'];
               }
            }

            //  Рублевые
            foreach ($k1_upsales as $key=>$u_operator) {
               if($item['key'] == $key){
                    $item['UP2_quantity'] += $u_operator['upsale_1_sum']+$u_operator['upsale_2_sum'];
               }
            }

            //  Полурублевые и Комплекты
            foreach ($k4_upsales as $key=>$u_operator) {
               if($item['key'] == $key){
                    $item['UP3_quantity'] += $u_operator['upsale_1_sum']+$u_operator['upsale_2_sum'];
               }
            }

            foreach ($k3_upsales as $key=>$u_operator) {
               if($item['key'] == $key){
                    $item['UP3_quantity'] += $u_operator['upsale_1_sum']+$u_operator['upsale_2_sum'];
               }
            }*/

/*
            foreach($b['group_by_categories_2']['buckets'] as $bi){
                if($bi['key'] == '1' || $bi['key'] == '5') {                            //  Без категории и Нутра
                    foreach ($bi['group_by_upsales_user_id']['buckets'] as $bo) {
                        foreach ($operator_ids as $operator_id){
                            if ($bo['key'] == $operator_id['key'])
                                $total_up1 += $bo['doc_count'];
                        }
                    }
                    $item['UP1_quantity'] = $total_up1;
                }
                if($bi['key'] == '3') {                                                 //  Рублевые
                    foreach ($bi['group_by_upsales_user_id']['buckets'] as $bo) {
                        foreach ($operator_ids as $operator_id){
                            if ($bo['key'] == $operator_id['key'])
                                $total_up2 += $bo['doc_count'];
                        }
                    }
                    $item['UP2_quantity'] = $total_up2;
                }
                if($bi['key'] == '2' || $bi['key'] == '4') {                            //  Полурублевые и Комплекты
                    foreach ($bi['group_by_upsales_user_id']['buckets'] as $bo) {
                        foreach ($operator_ids as $operator_id){
                            if ($bo['key'] == $operator_id['key'])
                                $total_up3 += $bo['doc_count'];
                        }
                    }
                    $item['UP3_quantity'] = $total_up3;
                }
            }
*/

            $item['RC1']  = $item['C1']  * $item['C1_quantity'];
            $item['RC2']  = $item['C2']  * $item['C2_quantity'];
            $item['RC3']  = $item['C3']  * $item['C3_quantity'];
            $item['RUP1'] = $item['UP1'] * $item['UP1_quantity'];
            $item['RUP2'] = $item['UP2'] * $item['UP2_quantity'];
            $item['RUP3'] = $item['UP3'] * $item['UP3_quantity'];
            $item['RT1']  = $item['T1']  * $item['T1_quantity'];
            $item['RT2']  = $item['T2']  * $item['T2_quantity'];
            $item['confirmed_upsales'] = $item['UP1_quantity'] + $item['UP2_quantity'] + $item['UP3_quantity'];

            $item['total_sum'] = $item['RC1'] + $item['RC2'] + $item['RC3']
                + $item['RUP1'] + $item['RUP2'] + $item['RUP3']
                + $item['RT1'] + $item['RT2'];                  // сумма всех показателей

            if ($item['total_sum'] > 0) $result['hits']['hits'][]['_source'] = $item;
        }
        $result['hits']['total'] = count($buckets);
        return $result;
    }

    private function dxBuildLogisticReport($result){
        $buckets = $result['aggregations']['group']['buckets'];

        foreach($buckets as $b){
            $item = [
                'key' => $b['key']
            ];

            if($b['doc_count']==0){
                continue;
            }

            if(isset($b['key_as_string'])){
                $date = date_create_from_format('Y/m/d H:i:s', $b['key_as_string']);
                $item['key'] =$date->format('d.m.Y');
            }

            foreach($b['status']['buckets'] as $bi){
    
                if($bi["key"]=="17"){
                    $item['status_group_17'] = $bi['doc_count'];        //Подтвержденные
                }

                // ЛОГИСТИКА

                if($bi["key"]=="77"){
                    $item['status_1'] = $bi['doc_count'];               //Заказ в ожидании
                }
                
                if($bi["key"]=="26"){
                    $item['status_2'] = $bi['doc_count'];               //Выкуплен
                }

                if($bi["key"]=="20"){
                    $item['status_3'] = $bi['summa']['value'];          //Деньги получены в кассу
                }

                if($bi["key"]=="46"){
                    $item['status_4'] = $bi['doc_count'];               //Возврат денег
                }

                // ПРОЗВОН
                
                if($bi["key"]=="38"){
                    $item['status_5'] = $bi['doc_count'];               //Говорит забрала
                }

                if($bi["key"]=="39"){
                    $item['status_6'] = $bi['doc_count'];               //Отказ
                }

                if($bi["key"]=="40"){
                    $item['status_7'] = $bi['doc_count'];               //Обещает забрать
                }

                if($bi["key"]=="41"){
                    $item['status_8'] = $bi['doc_count'];               //Не может забрать нужно помочь
                }

                if($bi["key"]=="42"){
                    $item['status_9'] = $bi['doc_count'];               //Недозвон
                }

                if($bi["key"]=="45"){
                    $item['status_10'] = $bi['doc_count'];              //Не берет трубку - не трогать
                }

                if($bi["key"]=="170"){
                    $item['status_group_18'] = $bi['doc_count'];        //Заказ на удаление
                }
                                                                        //Отказ не перезванивать - СТАТУСА НЕТ В БАЗЕ
                                                                        //Не пришел клиент на почту - СТАТУСА НЕТ В БАЗЕ
                                                                        //Не берет трубку - СТАТУСА НЕТ В БАЗЕ
                                                                        //Нет связи - СТАТУСА НЕТ В БАЗЕ
                                                                        //Отказался на складе новой почты - СТАТУСА НЕТ В БАЗЕ
                                                                        //Обязуется забрать позже - СТАТУСА НЕТ В БАЗЕ
                                                                        //Не оригинал - СТАТУСА НЕТ В БАЗЕ
                                                                        //Ошибочный заказ - СТАТУСА НЕТ В БАЗЕ
                                                                        //Нет в городе - СТАТУСА НЕТ В БАЗЕ
                                                                        //Нет денег - СТАТУСА НЕТ В БАЗЕ
                                                                        //Долгая доставка - СТАТУСА НЕТ В БАЗЕ
                                                                        //Передумал - СТАТУСА НЕТ В БАЗЕ
                                                                        //Ошибка оператора - СТАТУСА НЕТ В БАЗЕ
                                                                        //Оплата доставки - СТАТУСА НЕТ В БАЗЕ
                                                                        //Оплата доставки - СТАТУСА НЕТ В БАЗЕ

                if($bi["key"]=="105"){
                    $item['status_group_18_percent'] = $bi['doc_count'];      //Другое
                }

                if($bi["key"]=="21"){
                    $item['status_group_19'] = $bi['doc_count'];              //Деньги не получены
                }
                
                if($bi["key"]=="47"){
                    $item['status_group_19_percent'] = $bi['doc_count'];      //Деньги у доставщика
                }
            }

            foreach($b['transit']['buckets'] as $bi){
                if($bi["key"]=="true"){
                    $item['status_group_58'] = $bi['doc_count'];              //В пути
                }
            }
            
            $result['hits']['hits'][]['_source'] = $item;
        }

        $result['hits']['total'] = count($buckets);

        return $result;
    }
    private function dxBuildSalesAnalyticsReport($result){
        $buckets = $result['aggregations']['group']['buckets'];
        $params = request()->get('filter');
        $query = $this->dxQueryBuild(json_decode($params));
        $lte = "";

        foreach($query['bool']['must'] as $item){
            if(isset($item[0])) {
                if ((array_keys($item[0])[0]) == "range") {
                    $date_key = key($item[0]['range']);
                    if (array_keys($item[0]['range'][$date_key])[0] == "lte") {
                        $lte = Carbon::parse($item[0]['range'][$date_key]['lte']);
                    }
                }
            }
        }

        $date_minus_18 = $lte->subDays(18)->toDateTimeString();

        foreach ($buckets as $b) {
            $test = $this->analyze_array($b, $date_minus_18);
            $test['key'] = $b['key'];
            $result['hits']['hits'][]['_source'] = $test;
        }

        $result['hits']['total'] = count($buckets);

        return $result;
    }

    public function analyze_array($data, $date_minus_18){
        $result = [];
        if (isset($data['status_1c_3']['buckets'])) {
            $date_minus_18_total = 0;
            $item = [];

            foreach ($data['status_1c_3']['buckets'] as $key => $bi) {
                if ($bi['key'] == 'True') {
                    if ($bi['doc_count'] == 0)
                        continue;
                    $item = [
                        'key' => $data['key']
                    ];
                    $item['completed_orders'][] = $bi['doc_count'];
                    $completed_orders = $bi['doc_count'];

                    foreach ($bi['logistic_status']['buckets'] as $bk) {
                        $item['completed_orders'][] = $bk['doc_count'] . " - " . $bk['key'];
                        $temp = 0;

                        foreach ($bk['date_1c']['buckets'] as $bc) {
                            if (Carbon::parse($bc['key_as_string']) < Carbon::parse($date_minus_18)) {
                                $temp += $bc['doc_count'];
                                $date_minus_18_total += $bc['doc_count'];
                            }
                        }

                        $item['date_minus_18'][0] = $date_minus_18_total;
                        $item['date_minus_18'][] = $temp . " - " . $bk['key'];
                    }

                    $date_minus_18_status_26_total = 0;
                    $bought_out_total = 0;
                    foreach ($bi['status']['buckets'] as $bs) {
                        if ($bs['key'] == 26) {
                            $bought_out_total++;
                            foreach ($bs['date_1c']['buckets'] as $bc) {
                                if ($bc['key_as_string'] <= $date_minus_18) {
                                    $date_minus_18_status_26_total += $bc['doc_count'];
                                }
                            }
                        }
                    }

                    foreach ($bi['delivery_price']['buckets'] as $bd) {
                        $item['delivery_price'][] = $bd['key'] . " - " . $bd['doc_count'] . " заказа(-ов)";
                    }

                    $avg_add_margin_total = 0;
                    $margin_num = 0;
                    foreach ($bi['project_goal_price']['buckets'] as $bp) {
                        $item['project_goal_price'][] = $bp['key'];
                        $sum = 0;
                        foreach ($bp['quantity_price']['buckets'] as $bq) {
                            $sum += $bq['key'] * $bq['doc_count'];
                            $margin_num += $bq['doc_count'];
                        }
                        $sum -= $margin_num * $bp['key'];
                        $avg_add_margin_total += $sum;
                    }

                    if ($completed_orders > 0) {
                        $item['bought_out_percent'] = round(100 * $bought_out_total / $completed_orders, 1);
                        $item['avg_add_margin'] = round($avg_add_margin_total / $completed_orders, 1);
                    }

                    if ($date_minus_18_total > 0) {
                        $item['date_minus_18_percent'] = round(100 * $date_minus_18_status_26_total / $date_minus_18_total, 1);
                    }

                    $item['avg_check'] = round($bi['price_avg']['value'], 1);
                }
            }

            $result = $item;
        }
        else{
            $result['key'] = $data['key'];
            foreach ($data['group']['buckets'] as $d){
                if ($d['doc_count'] == 0)
                    continue;
                $temp = $this->analyze_array($d, $date_minus_18);
                if (!empty($temp))
                    $result['data'][] = $temp;
            }

        }

        return $result;
    }

    private function dxBuildAccountingReport($result){
        $buckets = $result['aggregations']['group']['buckets'];

        foreach($buckets as $b){
            $item = [
                'key' => $b['key']
            ];

            if($b['doc_count']==0){
                continue;
            }

            if(isset($b['key_as_string'])){
                $date = date_create_from_format('Y/m/d H:i:s', $b['key_as_string']);
                $item['key'] =$date->format('d.m.Y');
            }

            foreach($b['status']['buckets'] as $bi){
    
                if($bi["key"]=="26"){
                    $item['status_1'] = $bi['doc_count'];               //Выкуплен
                }

                if($bi["key"]=="20"){
                    $item['status_2'] = $bi['doc_count'];               //Деньги получены в кассу
                }

                if($bi["key"]=="21"){
                    $item['status_3'] = $bi['doc_count'];               //Деньги не получены
                }

                if($bi["key"]=="46"){
                    $item['status_4'] = $bi['doc_count'];               //Возврат денег
                }
                
                if($bi["key"]=="47"){
                    $item['status_5'] = $bi['doc_count'];               //Деньги у доставщика
                }
            }
            
            $result['hits']['hits'][]['_source'] = $item;
        }

        $result['hits']['total'] = count($buckets);

        return $result;
    }
    private function dxBuildFrodReport($result){
        $buckets = $result['aggregations']['group']['buckets'];
        foreach($buckets as $b){
            
            $item = [
                'key' => $b['key']
            ];
            if(isset($b['key_as_string'])){
                $date = date_create_from_format('Y/m/d H:i:s', $b['key_as_string']);
                $item['key'] =$date->format('d.m.Y');
            }

            if($b['doc_count']==0){
                continue;
            }

            foreach($b['status']['buckets'] as $bi){
                if($bi["key"]=="17"){
                    $item['status_group_17'] = $bi['doc_count'];                                            //Подтвержденные
                }

                if($bi["key"]=="50"){
                    $item['status_9'] = $bi['doc_count'];                                                   //Дубль
                }

                if($bi["key"]=="35"){
                    $item['status_1'] = $bi['doc_count'];                                                   //Недозвон
                    $item['status_2'] = 0;
                    if(isset($item['status_group_17']))
                        $item['status_2'] = round($bi['doc_count']/$item['status_group_17']*100,1);             //Недозвон %
                }

                if($bi["key"]=="36"){
                    $item['status_3'] = $bi['doc_count'];                                                   //Не фрод
                    $item['status_4'] = 0;
                    if(isset($item['status_group_17']))
                        $item['status_4'] = round($bi['doc_count']/$item['status_group_17']*100,1);             //Не фрод %
                }

                if($bi["key"]=="37"){
                    $item['status_5'] = $bi['doc_count'];                                                   //Отказ
                    $item['status_6'] = 0;
                    if(isset($item['status_group_17']))
                        $item['status_6'] = round($bi['doc_count']/$item['status_group_17']*100,1);             //Отказ %
                }

                if($bi["key"]=="48"){
                    $item['status_7'] = $bi['doc_count'];                                                   //Фрод
                    $item['status_8'] = 0;
                    if(isset($item['status_group_17']))
                        $item['status_8'] = round($bi['doc_count']/$item['status_group_17']*100,1);             //Фрод %
                }
                
                if($bi["key"]=="188"){
                    $item['status_10'] = $bi['doc_count'];                                                   //Предоплата
                    $item['upsale_1'] = 0;
                    if(isset($item['status_group_17']))
                        $item['upsale_1'] = round($bi['doc_count']/$item['status_group_17']*100,1);              //Предоплата %
                }
            }
            
            $result['hits']['hits'][]['_source'] = $item;
        }

        $result['hits']['total'] = count($buckets);

        return $result;
    }
    private function dxBuildCommonReport($result){
        $buckets = $result['aggregations']['group']['buckets'];
        foreach($buckets as $b){

            $item = [
                'key' => $b['key']
            ];

            if(isset($b['key_as_string'])){
                $date = date_create_from_format('Y/m/d H:i:s', $b['key_as_string']);
                $item['key'] =$date->format('d.m.Y');
            }

            $item['status_1']=$b['doc_count'];          // Лидов
            $item['status_9']=" 👁";                    // Лидов

            if($b['doc_count']==0){
                continue;
            }

            foreach($b['status']['buckets'] as $bi){
                if($bi["key"]=="17"){
                    $item['status_group_17'] = $bi['doc_count'];                                            //Аппрув
                    if($b['doc_count']>0)
                        $item['status_group_17_percent']= round($bi['doc_count']/$b['doc_count']*100,1);        //Аппрув %
                }

                if($bi["key"]=="22"){
                    $item['status_4'] = $bi['doc_count'];                                                   //Выкупов
                    if(isset($item['status_group_17']) && $item['status_group_17']>0)
                        $item['status_5'] = round($bi['doc_count']/$item['status_group_17']*100,1);         //Выкупов %
                }

                if($bi["key"]=="20"){
                    $item['status_6'] = $bi['summa']['value'];                                              //Деньги в кассе
                    $item['status_7'] = $bi['doc_count'];                                                   //Оборот
                    $item['status_8'] = round($item['status_6']/$item['status_7'],1);                       //Средний чек на выкупе
                }
            }
            $item['upsale_sum']=0;
            foreach($b['upsale']['buckets'] as $bu){
                if($bu['key'] == 1)
                     $item['upsale_sum'] = $bu['doc_count'];
                if($bu['key'] == 2)
                     $item['upsale_sum'] += $bu['doc_count'];                                               //Доп продажи
                if(isset($item['status_group_17']) && $item['status_group_17']>0)
                    $item['upsale_sum_percent'] =  round($item['upsale_sum']/$item['status_group_17']*100,1);   //Доп продажи %
            }
            foreach($b['transit']['buckets'] as $bi){
                if($bi["key"]=="true"){
                    $item['status_2'] = $bi['doc_count'];                                                   //В пути
                if(isset($item['status_group_17']) && $item['status_group_17']>0)
                    $item['status_3'] =  round($bi['doc_count']/$item['status_group_17']*100,1);            //В пути %
                }
            }
            $result['hits']['hits'][]['_source'] = $item;
        }

        $result['hits']['total'] = count($buckets);

        return $result;
    }
    public function dxBuildSalesReport($result){


        $buckets = $result['aggregations']['group']['buckets'];
        
        /*********************************
        *    Расчет апсейлов операторов. *
        **********************************/
        $upsale_operators = $result['aggregations']['status']['buckets'];//[0]['upsale_operators']['buckets'];

        $upsale_operators_value = $this->operatorUpsales($upsale_operators);

        foreach($buckets as $b){

            if($b['doc_count']==0)
                continue;

            $item = [
                'key' => $b['key'],
                'projects.title' => $b['key']
            ];

            if(isset($b['key_as_string'])){
                $date = date_create_from_format('Y/m/d H:i:s', $b['key_as_string']);
                $item['key'] =$date->format('d.m.Y');
                //$item['key'] =$date->format('Y-m-d');

            }

            foreach($b['group']['buckets'] as $bi){
                $item[$bi['key']] = $bi['doc_count'];
                $item[$bi['key']."_percent"] = 0;
                if($b['doc_count']>0){
                    $item[$bi['key']."_percent"] = round($bi['doc_count']/$b['doc_count']*100, 1);
                }
            }
            foreach($b['approved']['buckets'] as $bi){
                if($bi['key'] == 'true') {
                    $item['approved'] = $bi['doc_count'];
                    $item['approved_percent'] = round($bi['doc_count']/$b['doc_count']*100, 1);
                }
            }
            foreach($b['rejected']['buckets'] as $bi){
                if($bi['key'] == 'true') {
                    $item['status_group_19'] = $bi['doc_count'];
                    $item['status_group_19_percent'] = round($bi['doc_count'] / $b['doc_count'] * 100, 1);
                }
            }

            if(!isset($item['approved'])){
                $item['approved'] = 0;
            }
            if(!isset($item['rejected']))
                $item['rejected'] = 0;
            if(!isset($item['status_group_17'])){
                $item['status_group_17'] = 0;
            }

            if(!isset($item['status_group_17_percent'])){
                $item['status_group_17_percent'] = 0;
            }

            if(!isset($item['status_group_18'])){
                $item['status_group_18'] = 0;
            }

            if(!isset($item['status_group_18_percent'])){
                $item['status_group_18_percent'] = 0;
            }
                
            if(!isset($item['status_group_19']))
                $item['status_group_19'] = 0;
            if(!isset($item['status_group_58']))
                $item['status_group_58'] = 0;
            $item['status_group_17'] = $item['approved'];

            if(($b['doc_count']-$item['status_group_58'])>0){
                $item['expected_without_trash'] = round($item['status_group_18']/($b['doc_count']-$item['status_group_58'])*100, 1);
                $item['approve_clear'] = round($item['status_group_17']/($b['doc_count']-$item['status_group_58']), 2);
                if(($b['doc_count']-$item['status_group_58']-$item['status_group_18'])>0)
                    $item['approve_processed'] = round($item['status_group_17']/($b['doc_count']-$item['status_group_58']-$item['status_group_18']), 2);
                else $item['approve_processed'] = 0;
                $item['count_clear_percent'] = round(($b['doc_count']-$item['status_group_58'])/$b['doc_count']*100,1);
            }

            $item['count_clear'] = $b['doc_count']-$item['status_group_58'];
            $item['count_common'] = $b['doc_count'];

            $item['upsale_1'] =  0;
            $item['upsale_2'] =  0;

            $is_report_by_operator = false;
            foreach ($upsale_operators_value as $key=>$u_operator) {
               if($b['key'] == $key){
                    $is_report_by_operator = true;
                    $item['upsale_1'] = $u_operator['upsale_1_sum'];
                    $item['upsale_2'] = $u_operator['upsale_2_sum'];
               }
            }
            
            foreach($b['status']['buckets'] as $b_status){
                if($b_status['key'] == 17){
                    if(isset($b_status['upsale_1_sum']) && !$is_report_by_operator){
                        $item['upsale_1'] = round($b_status['upsale_1_sum']['value'], 0);
                    }
                    if(isset($b_status['upsale_2_sum']) && !$is_report_by_operator)
                        $item['upsale_2'] = round($b_status['upsale_2_sum']['value'], 0);
                    $item['price_avg'] = round($b_status['price_avg']['value'], 0);
                }
            }

            $item['upsale_sum'] = $item['upsale_1'] + $item['upsale_2'];

            if($item['status_group_17']>0)
                $item['upsale_sum_percent'] = round($item['upsale_sum']/$item['status_group_17']*100,1);

            if($b['doc_count']>0)
                $item['approve_common'] = round($item['status_group_17']/$b['doc_count'], 2);

            $result['hits']['hits'][]['_source'] = $item;
         }

         return $result;
    }

    public function operatorUpsales($upsale_operators, $by_category=false){


        foreach ($upsale_operators as $value) {
            if($value['key'] == 17){
                $upsale_operators = $value['upsale_operators']['buckets'];
                break;
            }
        }
        

        $upsale_operators_value = [];
        foreach ($upsale_operators as $value) {
            $item = json_decode($value['key']);

            if(!isset($upsale_operators_value[$item->title])){
                $upsale_operators_value[$item->title] = [];
            }

            //$upsale_operators_value[$item->title][] =['key' => $value['key'], 'count'=> $value['doc_count']];
            
            if($by_category){

                if(!$upsale_operators_value[$item->title])
                    $upsale_operators_value[$item->title] = [];                    

                if(!isset($upsale_operators_value[$item->title][$item->category]))
                    $upsale_operators_value[$item->title][$item->category] = 
                        [  'upsale_1_sum' =>0, 
                           'upsale_2_sum' =>0  ];
                
                $upsale_operators_value[$item->title][$item->category]['upsale_1_sum'] += $item->upsale_1_sum*$value['doc_count'];
                $upsale_operators_value[$item->title][$item->category]['upsale_2_sum'] += $item->upsale_2_sum*$value['doc_count'];

            }else{

                if(!$upsale_operators_value[$item->title]){
                    $upsale_operators_value[$item->title] = [
                        'upsale_1_sum' =>0, 
                        'upsale_2_sum' =>0 
                    ];
                }

                $upsale_operators_value[$item->title]['upsale_1_sum'] += $item->upsale_1_sum*$value['doc_count'];
                $upsale_operators_value[$item->title]['upsale_2_sum'] += $item->upsale_2_sum*$value['doc_count'];    

            }

        }

        //dd($upsale_operators_value);
        
        return $upsale_operators_value;
    }


    public function dxParamsToKey($items, $key){
        foreach($items['hits']['hits'] as $i){
            $i['_source']['key'] = $i['_source'][$key];
        }
        return $items;
    }

    public function dxSortBuild($dxSortItems){
        $sort  = [];
        foreach($dxSortItems as $s){
            $sort[]    = [
                $s->selector => $s->desc  ? 'desc' : 'asc'
            ];
        }
        return $sort;
    }

    public function dxQueryBuild($dxQueryItems){
        if(count($dxQueryItems) == 2){
            return $this->dxQueryBuild([$dxQueryItems[0],"=",$dxQueryItems[1]]);
        }
        if(gettype($dxQueryItems[0]) == "string" && $dxQueryItems[0] != "!"){
            return $this->dxQueryParseItem($dxQueryItems);
        }else{
            if(isset($dxQueryItems[1])){
                return $this->dxQueryParseGroup($dxQueryItems);
            }else{
                return $this->dxQueryBuild($dxQueryItems[0]);
            }
        }
    }

    public function dxQueryParseItem($dxQueryItems){
        $query = [];
        switch($dxQueryItems[1]){
            case "=":
                
                $dxQueryItemsArr = explode(".", $dxQueryItems[0]);

                if(!is_null($dxQueryItems[2]))
                if(count($dxQueryItemsArr)==1){
                    $query[]['term'][$dxQueryItems[0]] = $dxQueryItems[2];
                }else{

                    if($dxQueryItemsArr[1]=="Year"){
                        $query[]['term'][$dxQueryItemsArr[0]."_year"] = $dxQueryItems[2];
                    }

                    if($dxQueryItemsArr[1]=="Month"){
                        if(strlen($dxQueryItems[2])==1){
                            $dxQueryItems[2] = '0'.$dxQueryItems[2];
                        }

                        $query[]['term'][$dxQueryItemsArr[0]."_month"] = $dxQueryItems[2];
                    }

                    if($dxQueryItemsArr[1]!="Year" && $dxQueryItemsArr[1]!="Month"){
                        $query[]['term'][$dxQueryItems[0]] = $dxQueryItems[2];
                    }
                }

                if(is_null($dxQueryItems[2])){
                    $query[]['bool']['must_not']['exists']['field'] = $dxQueryItems[0];
                }

                break;
            case "<>":
                $query[]['bool']['must_not']['term'][$dxQueryItems[0]] = $dxQueryItems[2];
                break;
            case "<=":
                $query[]['range'][$dxQueryItems[0]]['lte'] = $dxQueryItems[2];
                break;
            case "<":
                $query[]['range'][$dxQueryItems[0]]['lt'] = $dxQueryItems[2];
                break;
            case ">=":
                $query[]['range'][$dxQueryItems[0]]['gte'] = $dxQueryItems[2];
                break;
            case ">":
                $query[]['range'][$dxQueryItems[0]]['gt'] = $dxQueryItems[2];
                break;
            case "contains":
                $query[]['wildcard'][$dxQueryItems[0]] = '*'.$dxQueryItems[2].'*';
                break;
            case "startswith":
                $query[]['wildcard'][$dxQueryItems[0]] = $dxQueryItems[2].'*';
                break;
            case "endswith":
                $query[]['wildcard'][$dxQueryItems[0]] = '*'.$dxQueryItems[2];
                break;
            case "notcontains":
                $query[]['bool']['must_not']['wildcard'][$dxQueryItems[0]] = '*'.$dxQueryItems[2].'*';
                break;
        }


        return $query;
    }

    public function dxQueryParseGroup($dxQueryItems){
        $query = [];
        
        if($dxQueryItems[0] == "!"){
            unset($dxQueryItems[0]);
            foreach($dxQueryItems as $i){
                if(gettype($i) != "string")
                    $query['bool']['must_not'][] = $this->dxQueryBuild($i);
            }
            return $query;
        }

        if($dxQueryItems[1] == "and"){
            foreach($dxQueryItems as $i){
                if(gettype($i) != "string")
                    $query['bool']['must'][] = $this->dxQueryBuild($i);
            }
        }
        if($dxQueryItems[1] == "or"){
            foreach($dxQueryItems as $i){
                if(gettype($i) != "string")
                    $query['bool']['should'][] = $this->dxQueryBuild($i);
            }
        }
        
        return $query;
    }

    public function dxDateFilterBuild($result){

        $dateTree = [];
        foreach($result['aggregations']['dt']['buckets'] as $i){
            $date = date_create_from_format('Y/m/d H:i:s', $i['key_as_string']);
            if($i['doc_count'] > 0)
                $dateTree[date_format($date, 'Y')][date_format($date, 'n')][date_format($date, 'j')] = 1;
        }

        $result['hits']['hits'] = [];
        $i=0;
        $j=0;
        $k=0;

        foreach($dateTree as $Ykey => $Y){
            $result['hits']['hits'][$i]['_source']['key'] = $Ykey;
            foreach($Y as $mkey => $m){
                $result['hits']['hits'][$i]['_source']['items'][$j]['key'] = $mkey;
                foreach($m as $dkey => $d){
                    $result['hits']['hits'][$i]['_source']['items'][$j]['items'][$k]['key'] = $dkey;
                    $k++;
                }
                $j++;
            }
            $i++;
        }

        return $result;
    }

    public function pgSearch($data){
        array_set($query, 'bool.must.match_all', new \stdClass());
        $sort  = [];
       
        if(isset($data['sort'])) 
            $sort = $this->dxSortBuild(json_decode($data['sort']));

        if(isset($data['filter'])) {
            $filter = $this->dxCorrectFilter(json_decode($data['filter']));
            $query = null;
            $query['constant_score']['filter']['bool']['must'] = $this->dxQueryBuild($filter);
        }
        
        $params = [
            'index'     => $this->getIndex(),
            'type'      => $this->getType(),
            'size'      => 1,
            'body' => [
                'sort'  => $sort,
                'query' => $query
            ]
        ];

        $params = $this->pgBuildParams($params, $data);

        $client     = $this->getElasticClient();
        $result     = $client->search($params);

        $group = json_decode($data['group']);
        if($group != null)
             $result = $this->pgBuildResult($result, $data);
        //dd($result);

        $collection = $this->hidrateSearchResult($result, null);

        return $collection;
    }

    private function pgBuildParams($params, $data){
        
        $group = json_decode($data['group']);
        if($group == null)
            return $params;

        $groups = [];

        foreach($group as $g){
            if(isset($g->groupInterval)){
                if(!$g->isExpanded)
                    $groups[] = $g;
            }else{
                $groups[]= $g;
            }
        }

        $groupSummary = json_decode($data['groupSummary']);

        $item = [];
        $old_item = null;

        for($i=count($groups)-1; $i>=0; $i--){
                
            $g=$groups[$i];

            if(isset($g->groupInterval)){
                $item = [
                    "group" => [
                        "date_histogram" => [
                                "field"=> $g->selector,
                                "interval"=> $g->groupInterval
                        ]
                    ]
                ];
            }else{
                $item = [
                    "group" => [
                        "terms" => [
                            "field"=> $g->selector
                        ]
                    ]
                ];
            }
            
            if($old_item != null)
                $item["group"]["aggs"] = $old_item;

            if(isset($groupSummary))
            foreach($groupSummary as $gs){
                $item["group"]["aggs"]["total_".$gs->summaryType] = [$gs->summaryType => [
                                                                            "field" => $gs->selector
                                                                        ]];
            }
            
            $old_item = $item;
        }
        $params['body']['aggs']= $item;

        return $params;
    }

    private function dxCorrectFilter($filter){
        foreach($filter as $k=>$f){
            if(gettype($f) != 'string')
            if(count($f)==1)
                $filter[$k]=$f[0];
        }
        return $filter;
    }

    private function pgGetDateTreeResult($buckets, $interval){
        $dItems = [];
        foreach($buckets as $b){
            $date = date_create_from_format('Y/m/d H:i:s', $b['key_as_string']);
            $dateY = date_format($date, 'Y');
            $dateM = date_format($date, 'n');
            $dateD = date_format($date, 'd');
            $dItems[$dateY][$dateM][$dateD] = [
                $b['total_sum']['value'],
                $b['doc_count']
            ];
        }

        $items = [];
        foreach($dItems as $year=>$dYear){
            $mItems = [];
            $ySummary = [];
            foreach($dYear as $month=>$dMonth){
                $dItems = [];
                $mSummary = [];
                foreach($dMonth as $day=>$dDay){
                    $dItem = [
                        'key' => $day,
                        'summary' => $dDay
                    ];
                    foreach($dDay as $key=>$summary ){
                        if(isset($ySummary[$key])){
                            $ySummary[$key] += $summary;
                        }else{
                            $ySummary[$key] = $summary;
                        }
                        if(isset($mSummary[$key])){
                            $mSummary[$key] += $summary;
                        }else{
                            $mSummary[$key] = $summary;
                        }
                    }
                    $dItems[] =$dItem;
                    if($interval == 'day')
                        $items[] = $dItem;
                }
                $mItem = [
                    'key' => $month,
                    'items' => $dItems,
                    'summary' => $mSummary
                ]; 
                $mItems[] = $mItem;
                if($interval == 'month')
                    $items[] = $mItem;
            } 
            $item = [
                'key'=> $year,
                'items'=> $mItems,
                'summary'=>$ySummary
            ];
            if($interval == 'year')
                $items[] = $item;
        }

        return $items;
    }  

    private function pgGetTreeResult($buckets, $interval){
        $items = [];
        foreach($buckets as $b){
            $item = [
                'key' =>$b['key'],
                'summary'=>[$b['total_sum']['value'], $b['doc_count']],
                'items' => null
            ];

            if(isset($b['group']) && !isset($b['group']['buckets'][0]['key_as_string']))
                $item['items']=$this->pgGetTreeResult($b['group']['buckets'], $interval);

            if(isset($b['group']) && isset($b['group']['buckets'][0]['key_as_string']))
                $item['items']=$this->pgGetDateTreeResult($b['group']['buckets'], $interval);
 
            $items[] =$item;
        }
        return $items;
    } 

    private function pgBuildResult($result, $data){

        $buckets = $result['aggregations']['group']['buckets'];
        $items = [];

        $group = json_decode($data['group']);
        $groupSummary = json_decode($data['groupSummary']);

        $groups = [];
        $groupInterval = null;
        $groupExpended = [];
        $groupExpendedInterval = [];
        $interval = null;

        foreach($group as $g){
            if(isset($g->groupInterval)){
                if( $interval == null){
                    $interval = $g->groupInterval;
                }
                if(!$g->isExpanded)
                    $groupInterval = $g;
            }else{
                $groups[]= $g;
            }
            /* if($g->isExpanded){
                if(isset($g->groupInterval)){
                    $filter =  $this->dxCorrectFilter(json_decode($data['filter']));
                    $groupExpendedInterval[] = $this->pgGetKeyByGroupSelector($filter, $g->selector, $g->groupInterval);
                }else{
                    $filter =  $this->dxCorrectFilter(json_decode($data['filter']));
                    $groupExpended[] = $this->pgGetKeyByGroupSelector($filter, $g->selector);
                }
            } */
        }

        //dd($groupExpendedInterval, $groupExpended);

        $result['hits']['hits'] = [];
        $buckets = $result['aggregations']['group']['buckets'];

        //Если только один элемент в группе значить итерация по времени (столбцы)
        if(count($group)==1){
            $i=0;
            foreach($buckets as $b){
                if(isset($b['key_as_string'])){
                    $date = date_create_from_format('Y/m/d H:i:s', $b['key_as_string']);
                    $item = $b['key_as_string'];
                    if($group[0]->groupInterval == 'year')
                        $item = date_format($date, 'Y');
                    if($group[0]->groupInterval == 'month')
                        $item = date_format($date, 'n');
                    if($group[0]->groupInterval == 'day')
                        $item = date_format($date, 'd');

                    $result['hits']['hits'][$i]['_source']['key'] = $item;
                }else{
                    $result['hits']['hits'][$i]['_source']['key'] = $b['key'];
                }

                $gsItem = [];
                if(isset($groupSummary))
                foreach($groupSummary as $gs){
                    $gsItem[] = $b["total_".$gs->summaryType]['value'];
                }
                $result['hits']['hits'][$i]['_source']['summary'] = $gsItem;
                $i++;
            }
        }
        else // Иначе по группам (строки)
        {
            $items = $this->pgGetTreeResult($buckets, $interval);

          /*   $result_by_key = [];
            foreach($buckets as $b){
                $date = date_create_from_format('Y/m/d H:i:s', $b['key_as_string']);
                // $dateItem = $b['key_as_string'];
                // if($groupInterval->groupInterval == 'year')
                    $dateY = date_format($date, 'Y');
                // if($groupInterval->groupInterval == 'month')
                    $dateM = date_format($date, 'n');
                // if($groupInterval->groupInterval == 'day')
                    $dateD = date_format($date, 'd');
                
                foreach($groups as $g){
                    foreach($b['group_by_'.$g->selector]['buckets'] as $secont_b){
                        $result_by_key[$secont_b['key']][$dateY][$dateM][$dateD]['count'] =  $secont_b['doc_count'];
                        foreach($groupSummary as $gs){
                            $result_by_key[$secont_b['key']][$dateY][$dateM][$dateD][$gs->summaryType] =  $secont_b["total_".$gs->selector."_".$gs->summaryType]['value'];
                        }
                    }
                }
            } */
               // dd($result_by_key);
            

            $result['hits']['hits'] = [];   
            foreach($items as $key=>$item){

               /*  $items = [];
                $globalSummary = [];
                $i=0;
                foreach($rbk as $year=>$itemsY){
                    $items[$i]['key'] = $year;
                    $ySummary = [];
                    $j=0;
                    foreach($itemsY as $month=>$itemsM){
                        $items[$i]['items'][$j]['key'] = $month;
                        $mSummary = [];
                        $k=0;
                        foreach($itemsM as $day=>$itemsD){
                            $items[$i]['items'][$j]['items'][$k]['key'] = $day;
                            
                            $dSummary = [];
                            $s = 0;
                            foreach($groupSummary as $gs){
                                $dSummary[] = $itemsD[$gs->summaryType];

                                if(isset($mSummary[$s])){
                                    $mSummary[$s] += $itemsD[$gs->summaryType];
                                }else{
                                    $mSummary[$s] = $itemsD[$gs->summaryType];
                                }

                                if(isset($ySummary[$s])){
                                    $ySummary[$s] += $itemsD[$gs->summaryType];
                                }else{
                                    $ySummary[$s] = $itemsD[$gs->summaryType];
                                }

                                if(isset($globalSummary[$s])){
                                    $globalSummary[$s] += $itemsD[$gs->summaryType];
                                }else{
                                    $globalSummary[$s] = $itemsD[$gs->summaryType];
                                }

                                $s++;
                            }
                            $items[$i]['items'][$j]['items'][$k++]['summary'] = $dSummary;
                        }
                        $items[$i]['items'][$j++]['summary'] = $mSummary;
                    }
                    $items[$i++]['summary'] = $ySummary;
                } */
                
                // dd($items);

                // foreach($rbk as $rbkKey => $r){
                //     $summary = [];

                //     $i=0;
                //     foreach($groupSummary as $gs){
                //         $summary[] = $r[$gs->summaryType];
                //         if(isset($globalSummary[$i])){
                //             $globalSummary[$i] += $r[$gs->summaryType];
                //         }else{
                //             $globalSummary[$i] = $r[$gs->summaryType];
                //         }
                //     }

                //     $items[] =[
                //         'key' => $rbkKey,
                //         'summary' => $summary
                //     ];
                // }

                // if($groupExpendedInterval != []){
                //     $items = [[
                //         'key' => $groupExpendedInterval,
                //         'items' => $items
                //     ]];
                // }
                
                $result['hits']['hits'][]['_source'] = [
                    'key' => $item['key'],
                    'items' => $item['items'],
                    'summary' => $item['summary']
                ];
            
            }

            //Это запрос для вложенных строк
          /*   if($groupExpended != []){
                $items = [];
                
                $key = $this->pgGetKeyByGroupSelector($filter, $groupExpended->selector);
                
                foreach($result['hits']['hits'] as $r){
                    $items[] = $r['_source'];
                }

                $result['hits']['hits'] = [];
                $result['hits']['hits'][]['_source'] = [
                    'key' => $key,
                    'items' => $items
                ];
            } */
        }

        return $result;
    }

/*     public function pgGetKeyByGroupSelector($filter, $selector, $groupInterval=null){
        $items = [];
        $result = [
            "key" => $selector,
            "value" => null
        ];
        if($groupInterval!=null)
            $result['interval'] = $groupInterval;

        foreach($filter as $f){
            if(gettype($f) != 'string'){
                if(gettype($f[0])=="string"){
                    $array = explode(".", $f[0]);
                    if(strtolower($array[1])==$groupInterval)
                        $f[0] = $array[0];
                }else{
                    $items = array_merge($items, $this->pgGetKeyByGroupSelector($f, $selector, $groupInterval));
                }
                if($f[0]==$selector){
                    $result['value'] = $f[2];
                    $items[] = $result;
                }
            }
        }
        return $items;
    } */

    public function searchById($id){

        $params = [
            'index'     => $this->getIndex(),
            'type'      => $this->getType(),
            'id'        => $id
        ];

        $client     = $this->getElasticClient();
        $result     = $client->get($params);                
        $collection = collect($result['_source']);

        return  $collection;       

    }

    public function suggest($query, $limit = 10, $filters = [], $field = 'suggest')
    {
        $params = [
            'index'     => $this->getIndex(),
            'type'      => $this->getType(),
            'body' => [
                'suggest'   => [
                    'data-suggest'  => [
                        'prefix'    => $query,
                        'completion'    => [
                            'field'     => $field,
                            'size'      => $limit
                        ]
                    ]
                ]
            ]
        ];


        if(!empty($filters)) {
            $keys       = collect(array_keys($filters))->sort()->toArray();
            if(count($keys) > 1) {

                $contextKey = implode('__', $keys);
                $parts      = [];


                //TODO: доделать для варианта множественного значения
                foreach ($keys as $key) {
                    $parts[]    = $filters[$key];
                }

                $params['body']['suggest']['data-suggest']['completion']['contexts']  = [
                    $contextKey => implode(':', $parts)
                ];
            }
            elseif(count($keys)) {
                $key    = $keys[0];
                $value  = $filters[$key];

                $params['body']['suggest']['data-suggest']['completion']['contexts']  = [
                    $key => $value
                ];
            }
        }



        if(request()->get('elastic_debug')) {
            return $params;
        }

        $client     = $this->getElasticClient();
        $result     = $client->search($params);


        if(!isset($result['suggest'])) {
            return [];
        }

        return $this->hidrateSuggestResult($result['suggest']['data-suggest']);
    }

    protected function hidrateSearchResult($result, $key=null)
    {
        $instance   = $this->model->newInstance();
        
        $collection = new SearchResultCollection([], $instance, $result, $this->getSearchRelations(), $key);

        return $collection;
    }

    protected function hidrateSuggestResult($result)
    {
        $instance   = $this->model->newInstance();
        $collection = new SuggestResultCollection([], $instance, $result, $this->getSearchRelations());

        return $collection;
    }

    public function reindex()
    {
        $relations  = $this->getSearchRelations();
        $this->model->chunk(1000, function($collection) use ($relations) {

            if(count($relations)) {
                $collection->load($relations);
            }

            foreach($collection as $model) {
                $this->reindexModel($model);
            }
        });
    }

    public function reindexByData($data)
    {
        $relations  = $this->getSearchRelations();        
        $datas = $data->chunk(1000);        
        foreach ($datas as $collection) {
            $client = $this->getElasticClient();
            $params = ['body' => []];
            foreach($collection as $model) {               
                $params['body'][] = [
                    'index' => [
                        '_index'    => $this->getIndex(),
                        '_type'     => $this->getType(),
                        '_id'       => $model->id
                    ]
                ];
                $params['body'][] = $this->prepareSearchData($model);             
            }           
            $client->bulk($params);            
        }        
    }

    public function searchByParamsScroll($query, $sort, int $page = 0, int $size = 10000, $isNeedHighlight = true ){

        $highlightFields    = [];

        if($isNeedHighlight)
            foreach ($this->getQueryFields() as $f) {
                $highlightFields[$f['field']]    = new \stdClass();
            }

        $params = [            
            "scroll" => "1m",
            "size" => $size,
            'index'     => $this->getIndex(),
            'type'      => $this->getType(),            
            'body' => [
                'query'     => $query,
                'sort'      => $sort,
                'highlight' => [
                    'pre_tags'  => '<em>',
                    'post_tags' => '</em>',
                    'fields'    => $highlightFields
                ]

            ]
        ];

        $client     = $this->getElasticClient();
        $result     = $client->search($params);        
        $scroll_id = $result['_scroll_id'];   
        
        if($page == 1) return $this->hidrateSearchResult($result);
        $i = 1;
        while (\true) {
            if($i == $page && $page != 0)break;
            $response = $client->scroll(
                array(
                    "scroll_id" => $scroll_id,
                    "scroll" => "1m"
                )
            );
            if (count($response['hits']['hits']) > 0) {
                $result['hits']['hits'] = array_merge($result['hits']['hits'], $response['hits']['hits']);

                // Get new scroll_id
                $scroll_id = $response['_scroll_id'];
            } else {
                // All done scrolling over data
                break;
            }
        
            $i++;
        }

        $collection = $this->hidrateSearchResult($result);

        return $collection;

    }

    public function deleteFromIndex($model)
    {
        $client = $this->getElasticClient();
        $params = [
            'index'     => $this->getIndex(),
            'type'      => $this->getType(),
            'id'        => $model->id
        ];

        $response = $client->delete($params);
    }

    public function deleteFromIndexbyQuery($params)
    {
        $client = $this->getElasticClient();
        $params = [
            'index'     => $this->getIndex(),
            'type'      => $this->getType(),
            'body'      => $params
        ];

        $response = $client->deleteByQuery($params);
    }

    public function reindexModel($model, $loadRelations = false)
    {
        $client = $this->getElasticClient();
        if(!($model instanceof Model)) {
            $model  = $this->find($model);
        }
        if($loadRelations) {
            $relations  = $this->getSearchRelations();

            $model->load($relations);
        }

        $params = [
            'index'     => $this->getIndex(),
            'type'      => $this->getType(),
            'id'        => $model->id,
            'body'      => $this->prepareSearchData($model)
        ];

        try {
            $response = $client->index($params);
            sleep(1);
        }
        catch(\Throwable $e) {
            dd($e->getMessage(), $params);
        }

        return $response;

    }

    public function prepareSearchData($model)
    {
        $data           = $model->toArray();

        return $data;
    }


    public function getType()
    {
        return $this->newModel->newInstance()->getTable();
    }

    public function getIndex()
    {
        return $this->newModel->newInstance()->getTable();
    }

    public function getMappings()
    {
        return [];
    }

    public function getQueryFields()
    {
        return [];
    }

    public function getSearchRelations()
    {
        return [];
    }

    public function getElasticClient()
    {
        return $this->elastic;
    }

    protected function getSuggestContextMapping()
    {
        $filters    = $this->getSuggestContextFilters(true);

        if(empty($filters)) {
            return [];
        }

        $mapping    = [];
        foreach ($filters as $name) {
            $mapping[]  = [
                'name'  => $name,
                'type'  => 'category',
//                'path'  => $name
            ];
        }

        return $mapping;
    }

    protected function getSuggestContext()
    {
        if(empty($this->suggestContexts)) {
            return [];
        }

        $sorted = collect($this->suggestContexts['context'])->sortBy('name')->toArray();

        return $sorted;
    }

    protected function getSuggestContextFilters($asString = false)
    {
        if(empty($this->suggestContexts)) {
            return [];
        }

        $filters    = [];
        foreach ($this->suggestContexts['filters'] as $filter) {
            $sorted     = collect($filter)->sort()->toArray();
            if($asString) {
                $filters[]  = implode('__', $sorted);
            }
            else {
                $filters[]  = $sorted;
            }
        }

        return $filters;
    }

    protected function getSuggestContextValues($data)
    {
        $filters    = $this->getSuggestContextFilters();

        $return = [];
        foreach ($filters as $filter) {
            $key                = implode('__', $filter);
            $fieldsValues       = [];
            foreach ($filter as $fieldKey) {
                $fieldValue                 = array_unique((array)array_get($data, $fieldKey, []));
                $fieldsValues[$fieldKey]    = $fieldValue;
            }

            $return[$key]   = [];



            $parts  = [];
            $first  = true;
            foreach ($fieldsValues as $fKey => $fValues) {
                $prevParts  = $parts;
                $newParts   = [];
                foreach ($fValues as $v) {
//                    $part       = $fKey . '_' . $v;
                    $part       = $v;

                    if($first) {
                        $newParts[]    = $part;
                    }
                    else {
                        foreach ($prevParts as $prevPart) {
                            $newParts[] = $prevPart . ':' . $part;
                        }
                    }

                    $parts  = $newParts;
                    $first  = false;
                }
            }


//            $return[$key][] = $parts;
            $return[$key] = $parts;
        }

        return $return;
    }

    private function getWordCount($string) {
        $string = preg_replace('/\s+/', ' ', trim($string));
        $words = explode(" ", $string);
        return count($words);
    }

}
