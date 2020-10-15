<?php
namespace App\Repositories;

use App\Models\Call;
use Illuminate\Support\Facades\DB;
use App\Helpers\Calls as CallsHelper;

class CallsRepository extends Repository
{
    public function model()
    {
        return Call::class;
    }

    public function getMappings()
    {
        $mappings   = [
            'id'    => [
                'type'  => 'keyword'
            ],
            'queue_id'    => [
                'type'  => 'integer'
            ],
            'step_id'   => [
                'type'  => 'integer'
            ],
            'order_id'    => [
                'type'  => 'integer'
            ],
            'rule_id'   => [
                'type'  => 'integer'
            ],
            'weight'   => [
                'type'  => 'integer'
            ],
            'ats_group_id'   => [
                'type'  => 'integer'
            ],
            'call_type'    => [
                'type'  => 'keyword'
            ],
            'user_id'    => [
                'type'  => 'integer'
            ],
            'sip'   => [
                'type'  => 'integer'
            ],
            'phone' => [
                'type'  => 'keyword'
            ],
            'dst' => [
                'type'  => 'keyword'
            ],
            'record_link'    => [
                'type'  => 'keyword'
            ],
            'record_time'    => [
                'type'  => 'integer'
            ],
            'time'    => [
                'type'      => 'date',
                'format'    => 'yyyy/MM/dd HH:mm:ss||yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'
            ],
            'billing_time'   => [
                'type'  => 'integer'
            ],
            'duration_time'   => [
                'type'  => 'integer'
            ],
            'disposition'    => [
                'type'      => 'keyword'
            ],     
            'order_key'    => [
                'type'  => 'keyword'
            ],
            /*'manager'   => [
                'type'  => 'keyword',
                'normalizer' => 'normalizer_keyword'
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
            ],*/
            'call_statuses'  => [
                'properties'    => [
                    'status'    => [
                        'type'  => 'keyword',
                        'normalizer' => 'normalizer_keyword'
                    ],
                    'agent' => [
                        'type'      => 'keyword'
                    ],
                    'time' => [
                        'type'      => 'date',
                        'format'    => 'dd.MM.yyyy HH:mm:ss||yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'
                    ]
                ]
            ],
            'call_statuses_string'    => [
                'type'      => 'keyword'
            ]
        ];

        return $mappings;
    }

    public function prepareSearchData($model)
    {
        $data   = [
            'id'                    => $model->id,
            //'key'                   => $model->id,
            'order_id'              => $model->order_id,
            'call_type'             => $model->call_type,
            'user_id'               => $model->user_id,
            'record_link'           => $model->record_link,
            'sip'                   => $model->sip,
            'disposition'           => $model->disposition,
            'ats_group_id'          => $model->ats_group_id,
            'phone'                 => $model->phone,
            'step_id'               => $model->step_id,
            'queue_id'              => $model->queue_id,
            'weight'                => $model->weight,
            'rule_id'               => $model->rule_id,
            'time'                  => ($model->time)?$model->time->format('Y-m-d H:i:s'):'',
            'billing_time'          => $model->billing_time,
            'duration_time'         => $model->duration_time
        ];

        
        $get_disposition_desc = CallsHelper::convertDisposition($model);
        $data['disposition_name'] = $get_disposition_desc['name'];
        $data['disposition_class'] = $get_disposition_desc['class'];
        

        $user = null;
        $data['manager']   = "";
        if($model->manager){
            //$user = DB::select('select * from users where phone_office = :phone_office', ['phone_office' => $model->sip]);
            /*if(isset($user[0]))
                $user = $user[0]->last_name.' '.$user[0]->first_name.' '.$user[0]->middle_name;*/
            $user = $model->manager->last_name.' '.$model->manager->first_name.' '.$model->manager->middle_name;
            $data['manager']   = $user;
        }
        
        $data['order_key'] = "";
       if($model->order){
            /*$order = DB::select('select * from orders where id = :id', ['id' => $model->order_id]);
            if(isset($order[0]))
                $data['order_key'] = $order[0]->key;*/
            $data['order_key'] = $model->order->key;

       }

        if($model->call_statuses) {
            $data['call_statuses']  = [];
            $data['call_statuses_string']  = "";

            $i=1;
            foreach ($model->call_statuses as $status) {
                $item =[
                    'status'    => CallsHelper::convertCallStatus($status->status),
                    'agent'   => $status->agent,
                    'user_id'   => $status->user_id                    
                ];
                if($status->time)$item['time'] = $status->time->format('Y-m-d H:i:s');                
                $data['call_statuses'][] = $item;

                $data['call_statuses_string'] .= $i.") ".$item['agent']." <span>".$item['status']."</span><br>".(($status->time)?$item['time']:'')."<br>";
                $i++;
            }
        }

        if($model->organization) {
            $data['organizations']   = [
                'id'    => $model->organization->id
            ];
        }

        if($model->time) {
            $data['time'] = $model->time->format('Y-m-d H:i:s');
        }

        return $data;
    }

    public function getSearchRelations()
    {
        return [
            'call_statuses'
        ];
    }

}
