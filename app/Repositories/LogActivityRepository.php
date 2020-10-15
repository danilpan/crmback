<?php
namespace App\Repositories;

use App\Models\LogActivity;

class LogActivityRepository extends Repository 
{
    public function model()
    {
        return LogActivity::class;
    }   

    public function getMappings()
    {            
        $mappings   = [
            'id'    => [
                'type'  => 'integer'
            ],
            'action'    => [
                'type'  => 'keyword'
            ],
            'url'    => [
                'type'  => 'keyword'                
            ],
            'referer'    => [
                'type'  => 'keyword'                
            ],
            'method'    => [
                'type'  => 'keyword'
            ],
            'info'    => [
                'type'  => 'keyword'
            ],
            'ip'    => [
                'type'  => 'keyword'
            ],
            'user_agent'    => [
                'type'  => 'keyword'
            ],
            'created_at'    => [
                'type'      => 'date',
                'format'    => 'yyyy/MM/dd HH:mm:ss||yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'
            ],
            'user_id'    => [
                'type'  => 'integer'
            ],
            'user_name'    => [
                'type'  => 'keyword',
                'normalizer' => 'normalizer_keyword'
            ],  
            'organizations'  => [
                'properties'    => [
                    'id'    => [
                        'type'  => 'keyword'
                    ]
                ]
            ]
        ];

        return $mappings;
    }

    public function prepareSearchData($model)
    {
        $data   = [
            'id'                   => $model->id,
            'action'               => $model->action,
            'url'                  => $model->url,
            'referer'              => $model->referer,
            'method'               => $model->method,
            'ip'                   => $model->ip,
            'user_agent'           => $model->user_agent,
            'info'                 => $model->info,
            'created_at'           => $model->created_at->format('Y-m-d H:i:s'),
            'user_id'              => ($model->user)?$model->user->id:1,
            'user_name'            => ($model->user)?$model->user->last_name." ".$model->user->first_name." ".$model->user->middle_name.'('.$model->user->organization->title.')':'Система'
        ]; 

        
        $data['organizations']   = [
            'id'    => ($model->user) ? $model->user->organization->id : 1
        ];        

        return $data;
    }

    public function getQueryFields()
    {
        $fields = [];

        return $fields;
    }
    
}  