<?php
namespace App\Repositories;

use App\Models\Order;
use App\Models\OrderImportIds;
use App\Models\Organization;
use Illuminate\Support\Facades\DB;

class OrdersRepository extends Repository
{
    protected $callsService;

    public function model()
    {
        return Order::class;
    }

    public function getSearchRelations()
    {
        return [
            'sites',
            'organization',
            'projects',
            'statuses',
            'device_type',
            'advert_source',
            'calls'
        ];
    }

    public function getQueryFields()
    {
        $fields = [
            [
                'field' => 'key',
                'type'  => 'terms'
            ],
            [
                'field' => 'import_id',
                'type'  => 'terms'
            ],
            [
                'field' => 'delivery_types_id',
                'type'  => 'terms'
            ],
            [
                'field' => 'organization_id',
                'type'  => 'terms'
            ],
            [
                'field' => 'import_webmaster_id',
                'type'  => 'terms'
            ],
            [
                'field' => 'transit_webmaster_id',
                'type'  => 'terms'
            ],
            [
                'field' => 'request_hash',
                'type'  => 'terms'
            ],
            [
                'field' => 'phones',
                'type'  => 'wildcard'
            ],
            [
                'field' => 'client_name',
                'type'  => 'wildcard'
            ],
            [
                'field' => 'address',
                'type'  => 'terms'
            ],
            [
                'field' => 'device_id',
                'type'  => 'terms'
            ],
            [
                'field' => 'source_id',
                'type'  => 'terms'
            ]
        ];

        return $fields;

//        return [
//            'key',
//            'import_id',
//            'import_webmaster_id',
//            'import_webmaster_transit_id',
//            'request_hash',
//            'api_key',
//            'phones',
//            'address',
//            'statuses.name'
//        ];
    }

    public function getMappings()
    {
        $mappings   = [
            'id'    => [
                'type'  => 'long'
            ],
            'age_id'    => [
                'type'  => 'long'
            ],
            'key'    => [
                'type'  => 'keyword'
            ],
            'import_id' => [
                'type'  => 'keyword'
            ],
            'delivery_types_id' => [
                'type'  => 'long'
            ],
            'organization_id' => [
                'type'  => 'long'
            ],
            'operator_id' => [
                'type'  => 'long'
            ],
            'manager_id' => [
                'type'  => 'long'
            ],
            'import_webmaster_id'   => [
                'type'  => 'keyword'
            ],
            'transit_webmaster_id'   => [
                'type'  => 'keyword'
            ],
            'webmaster_id'   => [
                'type'  => 'keyword'
            ],
            'webmaster_type'   => [
                'type'  => 'long'
            ],
            'request_hash'  => [
                'type'  => 'keyword'
            ],
            'type'  => [
                'type'  => 'keyword'
            ],
            'track_number'  => [
                'type'  => 'keyword'
            ],
            'dial_step' => [
                'type'  => 'long'
            ],
            'phones' => [
                'type'  => 'keyword'
            ],
            'time_zone' => [
                'type'  => 'keyword'
            ],
            'client_name' => [
                'type'  => 'keyword'
                    ],
            'sex_id'    => [
                'type'  => 'keyword'
            ],
            'country_code'  => [
                'type'  => 'keyword'
            ],
            'address'   => [
                'type'  => 'text'
            ],
            'created_at'    => [
                'type'      => 'date',
                'format'    => 'yyyy/MM/dd HH:mm:ss||yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'
            ],
            'created_at_year' => [
                'type'  => 'keyword'
            ],
            'created_at_month' => [
                'type'  => 'keyword'
            ],
            'ordered_at'    => [
                'type'      => 'date',
                'format'    => 'yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'
            ],
            'project_page_string' => [
                'type'      => 'keyword',
                'normalizer' => 'normalizer_keyword'
            ],

/*             'date_status_1'    => [
                'type'      => 'date',
                'format'    =>  'yyyy/MM/dd HH:mm:ss||yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'
            ],
            'date_status_2'    => [
                'type'      => 'date',
                'format'    =>  'yyyy/MM/dd HH:mm:ss||yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'
            ],
            'date_status_3'    => [
                'type'      => 'date',
                'format'    =>  'yyyy/MM/dd HH:mm:ss||yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'
            ],
            'date_status_4'    => [
                'type'      => 'date',
                'format'    =>  'yyyy/MM/dd HH:mm:ss||yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'
            ],
            'date_status_5'    => [
                'type'      => 'date',
                'format'    =>  'yyyy/MM/dd HH:mm:ss||yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'
            ],
             */
            'delivery_date_finish'    => [
                'type'      => 'date',
                'format'    =>  'yyyy/MM/dd HH:mm:ss||yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'
            ],

            'delivery_time_1'    => [
                "type" => "date",
                "format" => "HH:mm:ss"
            ],

            'delivery_time_2'    => [
                "type" => "date",
                "format" => "HH:mm:ss"
            ],

            'status_1c_1' => [
                'type'  => 'keyword'
            ],
            'status_1c_2' => [
                'type'  => 'keyword'
            ],
            'status_1c_3' => [
                'type'  => 'keyword'
            ],
            'status_1c_3_time'    => [
                'type'      => 'date',
                'format'    => 'yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'
            ],
            'geo'  => [
                'properties'    => [
                    'id'    => [
                        'type'  => 'long'
                    ],
                    'code' => [
                        'type'      => 'keyword'
                    ],
                    'name_en' => [
                        'type'      => 'keyword'
                    ],
                    'name_ru' => [
                        'type'      => 'keyword',
                        'normalizer' => 'normalizer_keyword'
                    ]
                ]
            ],
            'organizations'  => [
                'properties'    => [
                    'id'    => [
                        'type'  => 'keyword'
                    ],
                    'title' => [
                        'type'      => 'keyword',
                        'normalizer' => 'normalizer_keyword'
                    ]
                ]
            ],
            'operator'  => [
                'type'  => 'nested',
                'include_in_parent' => true,
                'properties'    => [
                    'id'    => [
                        'type'  => 'keyword'
                    ],
                    'title' => [
                        'type'      => 'keyword'
                    ],
                    'organizations'  => [
                        'type'  => 'nested',
                        'include_in_parent' => true,
                        'properties'    => [
                            'id'    => [
                                'type'  => 'keyword'
                            ],
                            'title' => [
                                'type'      => 'keyword',
                                'normalizer' => 'normalizer_keyword'
                            ]
                        ]
                    ]
                ]
            ],
            'delivery_type'  => [
                'properties'    => [
                    'id'    => [
                        'type'  => 'keyword'
                    ],
                    'title' => [
                        'type'      => 'keyword'
                    ]
                ]
            ],
            'sites'  => [
                'type'                  => 'nested',
                'include_in_parent'     => true,
                'properties'    => [
                    'id'    => [
                        'type'  => 'keyword'
                    ],
                    'url'   => [
                        'type'      => 'text',
                        'analyzer'  => 'url'
                    ]
                ]
            ],
            'project_page' => [
                'type'	=> 'nested',
                'include_in_parent' => true,
                    'properties' => [
                       'id'    => [
                            'type'  => 'keyword'
                        ],
                        'name' => [
                            'type' => 'keyword',
                            'normalizer' => 'normalizer_keyword'

                        ],
                        'import_id' => [
                            'type' => 'keyword'
                        ],
                        'link' => [
                            'type' => 'text',
                            'analyzer'  => 'link'
                        ]
                    ]
            ],             
            'dial_steps'   => [
                'type'                  => 'nested',
                'include_in_parent'     => true,
                'properties'    => [
                    'id'    => [
                        'type'  => 'long'
                    ],
                    'queue_id' => [
                        'type'     => 'long'
                    ],
                    'dial_step' => [
                        'type'      => 'long'
                    ],
                    'dial_time' => [
                        'type'      => 'date',
                        'format'    => 'yyyy/MM/dd HH:mm:ss||yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'                      
                    ]
                ]
            ],            
            'sms'   => [
                'type'                  => 'nested',
                'include_in_parent'     => true,
                'properties'    => [
                    'id'    => [
                        'type'  => 'integer'
                    ],
                    'user_id' => [
                        'type'     => 'integer'
                    ],
                    'sms_provider_id' => [
                        'type'      => 'integer'
                    ],
                    'status' => [
                        'type'      => 'keyword'
                    ],
                    'service_id' => [
                        'type'      => 'integer'
                    ],
                    'type' => [
                        'type'      => 'integer'
                    ],
                    'price' => [
                        'type'      => 'integer'
                    ],
                    'created_at' => [
                        'type'      => 'date',
                        'format'    => 'yyyy/MM/dd HH:mm:ss||yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'                      
                    ]
                ]
            ],
            'projects'   => [
                'type'                  => 'nested',
                'include_in_parent'     => true,
                'properties'    => [
                    'id'    => [
                        'type'  => 'keyword'
                    ],
                    'title' => [
                        'type'     => 'keyword',
                        'normalizer' => 'normalizer_keyword'
                    ],
                    'import_id' => [
                        'type'      => 'keyword'
                    ],
                    'desc' => [
                        'type'      => 'keyword'
                    ],
                    'name_en' => [
                        'type'      => 'keyword'
                    ],
                    'sms_sender' => [
                        'type'      => 'keyword'
                    ],
                    'countries' => [
                        'type'      => 'keyword'
                    ],
                    'hold' => [
                        'type'      => 'long'
                    ],
                    'sex' => [
                        'type'      => 'long'
                    ],
                    'category_id' => [
                        'type'      => 'long'
                    ],
                    'kc_category' => [
                        'type'      => 'long'
                    ],
                    'name_for_client' => [
                        'type'      => 'keyword'
                    ],
                    'project_category_kc_id' => [
                        'type' => 'keyword'
                    ]
                ]
            ],
            'project_category' => [
                'type'  => 'nested',
                'include_in_parent' => true,
                    'properties' => [
                       'id'    => [
                            'type'  => 'long'
                        ],
                        'title' => [
                            'type' => 'keyword'
                        ]
                    ]
            ],  
            'project_goal'  => [
                'properties'    => [
                    'id'    => [
                        'type'  => 'keyword'
                    ],
                    'is_private' => [
                        'type'      => 'boolean'
                    ],
                    'top_t' => [
                        'type'      => 'boolean'
                    ]
                ]
            ],   
            'comments'   => [
                'type'                  => 'nested',
                'include_in_parent'     => true,
                'properties'    => [
                    'id'    => [
                        'type'  => 'keyword'
                    ],
                    'content' => [
                        'type'      => 'keyword'
                    ],
                    'autor' => [
                        'type'      => 'keyword'
                    ]
                ]
            ],
            'calls'   => [
                'type'                  => 'nested',
                'include_in_parent'     => true,
                'properties'    => [
                    'id'    => [
                        'type'  => 'keyword'
                    ],
                    'queue_id'    => [
                        'type'  => 'integer'
                    ],
                    'step_id'   => [
                        'type'  => 'integer'
                    ],
                    'user_id'    => [
                        'type'  => 'integer'
                    ],
                    'phone' => [
                        'type'  => 'keyword'
                    ],
                    'duration_time'   => [
                        'type'  => 'integer'
                    ],
                    'disposition'    => [
                        'type'      => 'keyword'
                    ],
                    'organization_id' => [
                        'type' => 'integer'
                    ]
                ]
            ],
            'current_1_group_status_id' => [
                'type'  => 'keyword'
            ],
            'status_group_rejected' => [
                'type'  => 'keyword'
            ],
            'approved' => [
                'type' => 'keyword'
            ],
            'rejected' =>[
                'type' => 'keyword'
            ],
            'status_group_5' => [
                'type'  => 'keyword'
            ],
            'status_group_9' => [
                'type'  => 'keyword'
            ],
            'status_group_3_title' => [
                'type' => 'keyword'
            ],
            'project_goal_price' => [
                'type' => 'keyword'
            ],
            'goods_in_transit' => [
                'type' => 'keyword'
            ],
            'quantity_price_sum' => [
                'type' => 'long'
            ],
            'upsale_1_sum' => [
                'type' => 'long'
            ],
            'upsale_2_sum' => [
                'type' => 'long'
            ],
            'sales'   => [
                'type'                  => 'nested',
                'include_in_parent'     => true,
                'properties'    => [
                    'id' => [
                        'type'      => 'long'
                    ],
                    'product_id' => [
                        'type'      => 'long'
                    ],
                    'upsale' => [
                        'type'      => 'long'
                    ],
                    'upsale_user_id' => [
                        'type'      => 'long'
                    ],
                    'lead_id' => [
                        'type'      => 'long'
                    ],
                    'price'    => [
                        'type'  => 'float'
                    ],
                    'surplus_percent_price' => [
                        'type' => 'float'
                    ],
                    'quantity' => [
                        'type'      => 'long'
                    ],
                    'quantity_price' => [
                        'type'      => 'long'
                    ],
                    'quantity_pay' => [
                        'type'      => 'long'
                    ],
                    'weight' => [
                        'type'      => 'long'
                    ],
                    'cost_price' => [
                        'type'      => 'long'
                    ],
                    'comment' => [
                        'type'      => 'keyword'
                    ],
                    'name' => [
                        'type'      => 'keyword'
                    ],
                    'article' => [
                        'type'      => 'keyword'
                    ]
                ]
            ],
            'upsale_operator'   => [
                'type'                  => 'nested',
                'include_in_parent'     => true,
                'properties'    => [
                    'id' => [
                        'type'      => 'long'
                    ],
                    'title' => [
                        'type'      => 'keyword'
                    ],
                    'upsale_1_sum' => [
                        'type'      => 'long'
                    ],
                    'upsale_2_sum' => [
                        'type'      => 'long'
                    ],
                    'category' => [
                        'type'      => 'long'
                    ]
                ]
            ],
            'statuses'   => [
                'type'                  => 'nested',
                'include_in_parent'     => true,
                'properties'    => [
                    'id'    => [
                        'type'  => 'keyword'
                    ],
                    'parent_id' => [
                        'type'      => 'keyword'
                    ],
                    'name' => [
                        'type'      => 'keyword'
                    ],
                    'color' => [
                        'type'      => 'text'
                    ],
                    'autor' => [
                        'type'      => 'keyword'
                    ],
                    'created_at'    => [
                        'type'      => 'date',
                        'format'    =>  'yyyy/MM/dd HH:mm:ss||yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'
                    ],
                    'upsale_operator'   => [
                        'type'    => 'keyword'
                    ]
                ]
            ],
            'status_1' => [
                'type'  => 'nested',
                'include_in_parent' =>true,
                'properties'    => [
                    'id'    => [
                        'type'  => 'keyword'
                    ],
                    'parent_id' => [
                        'type'      => 'keyword'
                    ],
                    'name' => [
                        'type'      => 'keyword'
                    ],
                    'color' => [
                        'type'      => 'text'
                    ],
                    'autor' => [
                        'type'      => 'keyword'
                    ],
                    'created_at'    => [
                        'type'      => 'date',
                        'format'    =>  'yyyy/MM/dd HH:mm:ss||yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'
                    ]
                ]
            ],
            'status_2' => [
                'type'  => 'nested',
                'include_in_parent' =>true,
                'properties'    => [
                    'id'    => [
                        'type'  => 'keyword'
                    ],
                    'parent_id' => [
                        'type'      => 'keyword'
                    ],
                    'name' => [
                        'type'      => 'keyword'
                    ],
                    'color' => [
                        'type'      => 'text'
                    ],
                    'autor' => [
                        'type'      => 'keyword'
                    ],
                    'created_at'    => [
                        'type'      => 'date',
                        'format'    =>  'yyyy/MM/dd HH:mm:ss||yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'
                    ]
                ]
            ],
            'status_3' => [
                'type'  => 'nested',
                'include_in_parent' =>true,
                'properties'    => [
                    'id'    => [
                        'type'  => 'keyword'
                    ],
                    'parent_id' => [
                        'type'      => 'keyword'
                    ],
                    'name' => [
                        'type'      => 'keyword'
                    ],
                    'color' => [
                        'type'      => 'text'
                    ],
                    'autor' => [
                        'type'      => 'keyword'
                    ],
                    'created_at'    => [
                        'type'      => 'date',
                        'format'    =>  'yyyy/MM/dd HH:mm:ss||yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'
                    ]
                ]

                    ],
            'status_4' => [
                'type'  => 'nested',
                'include_in_parent' =>true,
                'properties'    => [
                    'id'    => [
                        'type'  => 'keyword'
                    ],
                    'parent_id' => [
                        'type'      => 'keyword'
                    ],
                    'name' => [
                        'type'      => 'keyword'
                    ],
                    'color' => [
                        'type'      => 'text'
                    ],
                    'autor' => [
                        'type'      => 'keyword'
                    ],
                    'created_at'    => [
                        'type'      => 'date',
                        'format'    =>  'yyyy/MM/dd HH:mm:ss||yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'
                    ]
                ]

                    ],
            'status_5' => [
                'type'  => 'nested',
                'include_in_parent' =>true,
                'properties'    => [
                    'id'    => [
                        'type'  => 'keyword'
                    ],
                    'parent_id' => [
                        'type'      => 'keyword'
                    ],
                    'name' => [
                        'type'      => 'keyword'
                    ],
                    'color' => [
                        'type'      => 'text'
                    ],
                    'autor' => [
                        'type'      => 'keyword'
                    ],
                    'created_at'    => [
                        'type'      => 'date',
                        'format'    =>  'yyyy/MM/dd HH:mm:ss||yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'
                    ]
                ]

                    ],
            'status_6' => [
                'type'  => 'nested',
                'include_in_parent' =>true,
                'properties'    => [
                    'id'    => [
                        'type'  => 'keyword'
                    ],
                    'parent_id' => [
                        'type'      => 'keyword'
                    ],
                    'name' => [
                        'type'      => 'keyword'
                    ],
                    'color' => [
                        'type'      => 'text'
                    ],
                    'autor' => [
                        'type'      => 'keyword'
                    ],
                    'created_at'    => [
                        'type'      => 'date',
                        'format'    =>  'yyyy/MM/dd HH:mm:ss||yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'
                    ]
                ]

                    ],
            'status_7' => [
                'type'  => 'nested',
                'include_in_parent' =>true,
                'properties'    => [
                    'id'    => [
                        'type'  => 'keyword'
                    ],
                    'parent_id' => [
                        'type'      => 'keyword'
                    ],
                    'name' => [
                        'type'      => 'keyword'
                    ],
                    'color' => [
                        'type'      => 'text'
                    ],
                    'autor' => [
                        'type'      => 'keyword'
                    ],
                    'created_at'    => [
                        'type'      => 'date',
                        'format'    =>  'yyyy/MM/dd HH:mm:ss||yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'
                    ]
                ]

                    ],
            'status_8' => [
                'type'  => 'nested',
                'include_in_parent' =>true,
                'properties'    => [
                    'id'    => [
                        'type'  => 'keyword'
                    ],
                    'parent_id' => [
                        'type'      => 'keyword'
                    ],
                    'name' => [
                        'type'      => 'keyword'
                    ],
                    'color' => [
                        'type'      => 'text'
                    ],
                    'autor' => [
                        'type'      => 'keyword'
                    ],
                    'created_at'    => [
                        'type'      => 'date',
                        'format'    =>  'yyyy/MM/dd HH:mm:ss||yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'
                    ]
                ]

                    ],
            'status_9' => [
                'type'  => 'nested',
                'include_in_parent' =>true,
                'properties'    => [
                    'id'    => [
                        'type'  => 'keyword'
                    ],
                    'parent_id' => [
                        'type'      => 'keyword'
                    ],
                    'name' => [
                        'type'      => 'keyword'
                    ],
                    'color' => [
                        'type'      => 'text'
                    ],
                    'autor' => [
                        'type'      => 'keyword'
                    ],
                    'created_at'    => [
                        'type'      => 'date',
                        'format'    =>  'yyyy/MM/dd HH:mm:ss||yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'
                    ]
                ]

                    ],
            'status_10' => [
                'type'  => 'nested',
                'include_in_parent' =>true,
                'properties'    => [
                    'id'    => [
                        'type'  => 'keyword'
                    ],
                    'parent_id' => [
                        'type'      => 'keyword'
                    ],
                    'name' => [
                        'type'      => 'keyword'
                    ],
                    'color' => [
                        'type'      => 'text'
                    ],
                    'autor' => [
                        'type'      => 'keyword'
                    ],
                    'created_at'    => [
                        'type'      => 'date',
                        'format'    =>  'yyyy/MM/dd HH:mm:ss||yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'
                    ]
                ]
            ],
            'project_category_kc'  => [
                'type'	=> 'nested',
                'include_in_parent' => true,
                'properties'    => [
                    'id'    => [
                        'type'  => 'keyword'
                    ],
                    'title' => [
                        'type'  => 'keyword'
                    ]
                ]
            ],
            'device_id' => [
                'type'  => 'long'
            ],
            'source_id' => [
                'type'  => 'long'
            ],
            'is_double' => [
                'type'  => 'keyword'
            ],
            'delivery_price' => [
                'type'  => 'long'
            ],
            'order_sender_id' =>[
                'type'  => 'long'
            ]
        ];

        return $mappings;
    }

    public function prepareSearchData($model)
    {
//            'country_code'  => [
//        'type'  => 'keyword'
//    ],
//            'address'   => [
//        'type'  => 'text'
//    ],
//            'created_at'    => [
//        'type'      => 'date',
//        'format'    => 'yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'
//    ],
//            'ordered_at'    => [
//        'type'      => 'date',
//        'format'    => 'yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'
//    ],

        $data   = [
            'id'                            => $model->id,
            'age_id'                        => $model->age_id,
            'key'                           => $model->key,
            'import_id'                     => $model->import_id,
            'delivery_types_id'             => $model->delivery_types_id,
            'organization_id'               => $model->organization_id,
            'operator_id'                   => $model->operator_id,
            'manager_id'                    => $model->manager_id,
            'import_webmaster_id'           => $model->import_webmaster_id,
            'transit_webmaster_id'          => $model->transit_webmaster_id,
            'webmaster_type'                => $model->webmaster_type,
            'request_hash'                  => $model->request_hash,
            'type'                          => $model->type,
            'dial_step'                     => $model->dial_step,
            'country_code'                  => $model->country_code,
            'delivery_time_1'               => $model->delivery_time_1,
            'delivery_time_2'               => $model->delivery_time_2,
            'delivery_types_price'          => $model->delivery_types_price,
            'surplus_percent_price'          => $model->surplus_percent_price,
            'region'                        => $model->region,
            'area'                          => $model->area,
            'city'                          => $model->city,
            'street'                        => $model->street,
            'home'                          => $model->home,
            'room'                          => $model->room,
            'housing'                       => $model->housing,
            'postcode'                      => $model->postcode,
            'warehouse'                     => $model->warehouse,
            'warehouse_id'                  => $model->warehouse_id,
            'time_zone'                     => $model->time_zone,
            'client_email'                  => $model->client_email,
            'track_number'                  => $model->track_number,
            'site_order_id'                 => $model->site_order_id,
            'delivery_price'                => $model->delivery_types_price,
            'products_total'                => $model->products_total,
            'upsale1'                       => $model->upsale1,
            'upsale2'                       => $model->upsale2,
            'cost_main'                     => $model->cost_main,
            'status_old_crm'                => $model->status_old_crm,
            'responsible_id'                => $model->responsible_id,
            'gasket_id'                     => $model->gasket_id,
            'webmaster_id'                  => $model->webmaster_id,
            'flow_id'                       => $model->flow_id,
            'real_profit'                   => $model->real_profit,
            'time_zone'                     => $model->time_zone,
            'sex_id'                        => $model->sex_id,
            'second_id'                     => $model->second_id,
            'profit'                        => $model->profit,
            'is_unload'                     => $model->is_unload,
            'device_id'                     => $model->device_id,
            'source_id'                     => $model->source_id,
            'is_double'                     => $model->is_double,
            'order_sender_id'               => $model->order_sender_id,
            'status_1c_1'                   => $model->status_1c_1,
            'status_1c_2'                   => $model->status_1c_2,
            'status_1c_3'                   => $model->status_1c_3
        ];

        $import_ids = OrderImportIds::where('order_id', $model->id)->get()->pluck('import_id')->toArray();

        if(count($import_ids)>0){
            $data['import_ids']= $import_ids;
        }else{
            $data['import_ids'][] = $model->import_id;
        }

        if($model->status_1c_3_time !=null){
            $data['status_1c_3_time'] = $model->status_1c_3_time->format('Y-m-d H:i:s');
        }

//
//        $table->string('full_address')->nullable();
//        $table->string('region')->nullable();
//        $table->string('area')->nullable();
//        $table->string('city')->nullable();
//        $table->string('street')->nullable();
//        $table->string('home')->nullable();
//        $table->string('room')->nullable();
//        $table->string('postcode')->nullable();
//
//        $table->string('info')->nullable();

        if($model->full_address) {
            $data['full_address']    = $model->full_address;
        }
        else {
            $keys   = ['region', 'area', 'city', 'street', 'home', 'room', 'postcode'];
            $parts  = array_filter(array_only($model->toArray(), $keys), function($val){
                return !empty($val);
            });

            if(count($parts)) {
                $data['full_address']    = implode(' ', $parts);
            }
        }

        if(!empty($model->phones)) {
            $data['phones']  = $model->phones;
        }

        if(!empty($model->client_name)) {
            $data['client_name']  = $model->client_name;
        }

        if($model->sites) {
            $data['sites']  = [];
            foreach ($model->sites as $site) {
                $data['sites'][]    = [
                    'id'    => $site->id,
                    'url'   => $site->url
                ];
            }
        }


        if($model->organization) {
            $data['organizations']   = [
                'id'    => $model->organization->id,
                'title' => $model->organization->title
            ];
        }

        $data['operator'] = null;
        if($model->manager) {
            $data['operator']   = [
                'id'    => $model->manager->id,
                'title' => $model->manager->last_name." ".$model->manager->first_name." ".$model->manager->middle_name,
                'organizations' => [
                    'id'    => $model->manager->organization->id,
                    'title' => $model->manager->organization->title
                ]
            ];
        }

        $data['delivery_type']=null;
        if($model->delivery_types) {
            $data['delivery_type']   = [
                'id'    => $model->delivery_types->id,
                'title' => $model->delivery_types->name
            ];
        }

        $data['projects']  = [];
        $data['project_category_kc']  = [];
        $data['project_category']  = [];
        $data['project_page'] = [];
        $data['dial_steps'] = [];
        $data['project_page_string'] = "";

        if($model->dial_steps) {
            foreach ($model->dial_steps as $dial_step) {
                $data['dial_steps'][]  = [
                    'id' => $dial_step->id,
                    'queue_id' => $dial_step->queue_id,                    
                    'dial_step' => $dial_step->dial_step,
                    'dial_time' => strtotime($dial_step->dial_time->format('Y-m-d H:i:s')),                    
                ];               
            }            
        } 
        if (count($data['dial_steps']) == 0) {
            $data['dial_steps'][]  = [
                    'id' => 0,
                    'queue_id' => null,                    
                    'dial_step' => 0,
                    'dial_time' => strtotime('now'),                    
                ];
        }

        if($model->projects) {

            foreach ($model->projects as $project) {
                $data['projects'][]  = [
                    'id' => $project->id,
                    'title' => $project->title,
                    'import_id' => $project->import_id,
                    'name_en' => $project->name_en,
                    'description' => $project->description,
                    'sms_sender' => $project->sms_sender,
                    'countries' => $project->countries,
                    'hold' => $project->hold,
                    'sex' => $project->sex,
                    'kc_category' => $project->kc_category,
                    'category_id' => $project->category_id,
                    'project_category_kc_id' => $project->project_category_kc_id,
                    'project_category_id' => $project->project_category_id,
                    'name_for_client' => $project->name_for_client
                ];

                if($project->project_category_kc)
                    $data['project_category_kc'][] = $project->project_category_kc;

                if($project->category)
                    $data['project_category'][] = $project->category;

                $data['project_goal_price'] = 0;
                if ($data['project_goal_price'] == 0 && $project->project_goals){
                    foreach ($project->project_goals as $project_goal){
                        if($project_goal->price && $data['project_goal_price'] == 0) {
                            $data['project_goal_price'] = $project_goal->price;
                        }
                    }
                }

            }

            $data['projects_title']  = "";
        }

        if($model->project_pages){
            foreach($model->project_pages as $page){
                $data['project_page'][] = [
                    'id'  => $page->id,
                    'project_id'  => $page->project_id,
                    'name'	=> $page->name.' '.$page->link,
                    'link'	=> $page->link,
                    'import_id'	=> $page->import_id
                ];

                $data['project_page_string'] =  $page->name.' '.$page->link;
            }
        }

        $data['project_goal']=null;
        if($model->project_goal) {
            $data['project_goal']   = [
                'id'    => $model->project_goal->id,
                'is_private' => $model->project_goal->is_private,
                'top_t' => $model->project_goal->top_t
            ];
        }

        $data['sms'] = [];
        if($model->sms) {
            foreach ($model->sms as $sms) {
                $data['sms'][]  = [
                    'id' => $sms->id,
                    'user_id' => $sms->user_id,
                    'sms_provider_id' => $sms->sms_provider_id,
                    'status' => $sms->status,
                    'service_id' => $sms->service_id,
                    'type' => $sms->type,
                    'price' => $sms->price,
                    'created_at' => $sms->created_at->format('Y-m-d H:i:s')
                ];     
            }
        }

        if($model->comments) {
            $data['comments']  = [];

            foreach ($model->comments as $comment) {
                $user = null;
                if($comment->user_id){
                    $user = DB::select('select * from users where id = :user_id', ['user_id' => $comment->user_id]);
                    if(isset($user[0]))
                        $user = $user[0]->last_name.' '.$user[0]->first_name.' '.$user[0]->middle_name;
                }

                $data['comments'][]    = [
                    'id'    => $comment->id,
                    'content' => $comment->text,
                    'autor' => $user,
                    'create_date' => $comment->created_at->format('Y-m-d H:i:s')
                ];

            }
        }   
       
        $data['quantity_price_sum'] = 0;
        $data['upsale_1_sum'] = 0;
        $data['upsale_2_sum'] = 0;
        $user_id_upsale = [];
        $upsale_operators  = [];
        $data['upsale_operator'] = [];

        if($model->sales) {

            $data['sales']  = [];

            foreach ($model->sales as $sale) {
                $data['sales'][]    = [
                    'id' => $sale->id,
                    'product_id' => $sale->product_id,
                    'upsale' => $sale->upsale,
                    'upsale_user_id' => $sale->upsale_user_id,
                    'lead_id' => $sale->lead_id,
                    'quantity' => $sale->quantity,
                    'quantity_price' => $sale->quantity_price,
                    'quantity_pay' => $sale->quantity_pay,
                    'weight' => $sale->weight,
                    'cost_price' => $sale->cost_price,
                    'price' => $sale->price,
                    'comment' => $sale->comment,
                    'name' => $sale->name,
                    'article' => $sale->article
                ];

                $data['quantity_price_sum'] += $sale->quantity_price;

                if(!isset($user_id_upsale[$sale->upsale_user_id]))
                            $user_id_upsale[$sale->upsale_user_id] = ['upsale_1_sum'=>0,'upsale_2_sum'=>0];
                
                if($sale->upsale == 1){
                    $user_id_upsale[$sale->upsale_user_id]['upsale_1_sum']++; 
                    $data['upsale_1_sum']++;
                }
                if($sale->upsale == 2){
                    $user_id_upsale[$sale->upsale_user_id]['upsale_2_sum']++; 
                    $data['upsale_2_sum']++;
                }
            }

            if(count($user_id_upsale)>0){
                foreach ($user_id_upsale as $key => $value) {
                    $user = DB::select('select * from users where id = :user_id', ['user_id' => $key]);
                    if($user != null){
                        
                        $upsale_operator = [
                            'id' => $key,
                            'title' => $user[0]->last_name." ".$user[0]->first_name." ".$user[0]->middle_name,
                            'upsale_1_sum' => $value['upsale_1_sum'],
                            'upsale_2_sum' => $value['upsale_2_sum']
                        ];
                        
                        if(isset($data['project_category_kc'][0]))
                            $upsale_operator['category'] = $data['project_category_kc'][0]->id;

                        $data['upsale_operator'][] = $upsale_operator;
                        $upsale_operators[] = json_encode($upsale_operator);
                    }
                }
            }
        }

        if($model->statuses) {
            $data['statuses']  = [];
            $data['goods_in_transit'] = "false";
            $data['approved'] = "false";
            $data['rejected'] = "false";

            foreach ($model->statuses as $status) {

                $user = null;
                if($status->pivot->user_id){
                    $user = DB::select('select * from users where id = :user_id', ['user_id' => $status->pivot->user_id]);

                    if(isset($user[0]))
                        $user = $user[0]->last_name.' '.$user[0]->first_name.' '.$user[0]->middle_name;
                }

                if($status->type == 3){
                    $data['status_group_3_title'] = $status->title;
                }

                $data['statuses'][]    = [
                    'id'    => $status->id,
                    'parent_id' => $status->parent_id,
                    'name' => $status->name,
                    'color' => $status->color,
                    'autor' => $user,
                    'created_at' => $status->pivot->created_at->format('Y-m-d H:i:s'),
                    'upsale_operator' => ($status->id == 17) ? $upsale_operators : null
                ];

                $data['status_'.$status->type]   = [
                    'id'    => $status->id,
                    'parent_id' => $status->parent_id,
                    'name' => $status->name,
                    'color' => $status->color,
                    'autor' => $user,
                    'created_at' => $status->pivot->created_at->format('Y-m-d H:i:s')
                ];

                if($status->parent_id == 18 || $status->parent_id == 19 || $status->parent_id == 58){
                    $data['current_1_group_status_id']='status_group_'.$status->parent_id;
                }

                if($status->id == 17)
                    $data['current_1_group_status_id']='status_group_'.$status->id;

                if($status->id == 22 || $status->parent_id == 107 || $status->parent_id == 114 || $status->parent_id == 115 || $status->parent_id == 166)
                    $data['goods_in_transit'] = "true";

                if($status->type == 5)
                    $data['status_group_5']='status_group_'.$status->id;

                if($status->type == 9)
                    $data['status_group_9']='status_group_'.$status->id;

                if($status->id == 19 || $status->parent_id == 19)
                    $data['status_group_rejected'] = 'status_group_'.$status->id;
            }

            if(!isset($data['current_1_group_status_id']))
                $data['current_1_group_status_id']='status_group_18';
            if(!isset($data['status_group_5']))
                $data['status_group_5']='';
            if(!isset($data['status_group_9']))
                $data['status_group_9']='';
            if(!isset($data['status_group_rejected']))
                $data['status_group_rejected']='';

            if($data['current_1_group_status_id'] == 'status_group_17'
                &&  ($data['status_group_5'] != 'status_group_218'
                    && (($data['status_group_9'] != 'status_group_227' && $data['status_group_9'] != 'status_group_231')
                        || $data['status_group_5'] == 'status_group_36')
                ))
                $data['approved'] = "true";

            if($data['status_group_rejected'] != 0
                &&  ($data['status_group_5'] == 'status_group_218'
                    || (($data['status_group_9'] == 'status_group_227' || $data['status_group_9'] == 'status_group_231')
                        && $data['status_group_5'] == 'status_group_36')
                ))
                $data['rejected'] = "true";
        }

        if($model->country_code){
            $country_code = DB::select('select * from geo where code = :code', ['code' => $model->country_code]);
            if(isset($country_code[0]))
                $data['geo'] = $country_code[0]->name_ru;
        }

        if($model->created_at) {
            $data['created_at'] = $model->created_at->format('Y-m-d H:i:s');
        }

        if($model->created_at) {
            $data['created_at_year'] = $model->created_at->format('Y');
        }

        if($model->created_at) {
            $data['created_at_month'] = $model->created_at->format('m');
        }

        if($model->ordered_at) {
            $data['ordered_at'] = $model->ordered_at->format('Y-m-d H:i:s');
        }

        if($model->dial_time != null){
            $data['dial_time'] = $model->dial_time->format('Y-m-d H:i:s');
        }

/*         if($model->date_status_1 != null){
            $data['date_status_1'] = $model->date_status_1->format('Y-m-d H:i:s');
        }
        if($model->date_status_2  != null){
            $data['date_status_2'] = $model->date_status_2->format('Y-m-d H:i:s');
        }
        if($model->date_status_3  != null) {
            $data['date_status_3'] = $model->date_status_3->format('Y-m-d H:i:s');
        }
        if($model->date_status_4  != null) {
            $data['date_status_4'] = $model->date_status_4->format('Y-m-d H:i:s');
        }
        if($model->date_status_5  != null) {
            $data['date_status_5'] = $model->date_status_5->format('Y-m-d H:i:s');
        }
 */
        if($model->delivery_date_finish  != null) {
            $data['delivery_date_finish'] = $model->delivery_date_finish->format('Y-m-d H:i:s');
        }

        if($model->geo) {
            $data['geo'] = [
                    'id'    => $model->geo->id,
                    'code' => $model->geo->code,
                    'name_en' => $model->geo->name_en,
                    'name_ru' => $model->geo->name_ru
                ];
        }

        // if($model->projects) {

        //     $data['projects_title']  = null;
        //     $data['project_goal_script'] = null;

        //     foreach ($model->projects as $project) {
        //         $data['projects'][]  = [
        //             'id' => $project->id,
        //             'title' => $project->title,
        //             'import_id' => $project->import_id,
        //             'name_en' => $project->name_en,
        //             'description' => $project->description,
        //             'sms_sender' => $project->sms_sender,
        //             'countries' => $project->countries,
        //             'hold' => $project->hold,
        //             'sex' => $project->sex,
        //             'kc_category' => $project->kc_category,
        //             'category_id' => $project->category_id,
        //             'project_category_kc_id' => $project->project_category_kc_id,
        //             'project_category_id' => $project->project_category_id,
        //             'name_for_client' => $project->name_for_client
        //         ];

        //         if($project->project_category_kc)
        //             $data['project_category_kc'][] = $project->project_category_kc;

        //         if($project->category)
        //             $data['project_category'][] = $project->category;

        //         if($data['projects_title'] == null)
        //             $data['projects_title'] = $project->title;

        //         if(isset($data['geo']) && $project->project_goals){
        //             foreach ($project->project_goals as $goal){
        //                 if($goal->geo_id == $data['geo']['id']){
        //                     if($goal->project_goal_scripts) {
        //                         foreach($goal->project_goal_scripts as $script){
        //                             if($script->status == true){
        //                                 $data['project_goal_script'] = $script->name;
        //                                 break 2;
        //                             }
        //                         }
        //                     }
        //                 }
        //             }
        //         }
        //     }
        // }

        $data['calls'] = [];
        // if($model->calls) {
        //     foreach ($model->calls as $call) {
        //         $data['calls'][]  = [
        //             'id' => $call->id,
        //             'user_id' => $call->user_id,
        //             'queue_id' => $call->queue_id,
        //             'duration_time' => $call->duration_time,
        //             'dial_step' => $call->dial_step,
        //             'disposition' => $call->disposition,
        //             'phone' => $call->phone,
        //             'organization_id' => $call->organization_id
        //         ];
        //     }
        // }

        return $data;
    }

}
