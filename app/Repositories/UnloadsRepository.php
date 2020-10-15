<?php
namespace App\Repositories;

use App\Models\Unload;

class UnloadsRepository extends Repository
{
    public function model()
    {
        return Unload::class;
    }

      public function getMappings()
    {
        $mappings   = [
            'id'    => [
                'type'  => 'integer'
            ],
            'organization_id'    => [
                'type'  => 'integer'
            ],
            'name'    => [
                'type'  => 'keyword'
            ],
            'comment'    => [
                'type'  => 'keyword'
            ],
            'config'    => [
                'type'  => 'text'
            ],
            'api_key'    => [
                'type'  => 'keyword'
            ],
            'is_work'    => [
                'type'  => 'boolean'
            ],
            'organizations'  => [
                'properties'    => [
                    'id'    => [
                        'type'  => 'keyword'
                    ],
                    'title' => [
                        'type'      => 'keyword'
                    ]
                ]
            ]
        ];

        return $mappings;
    }
    
    public function prepareSearchData($model)
    {
        $data = [
            'id'                => $model->id,
            'name'       	    => $model->name,
            'comment'           => $model->comment,
            'config'            => $model->config,
            'api_key'           => $model->api_key,
            'is_work'           => $model->is_work,
            'organization_id'   => $model->organization_id
        ];

        if($model->organization) {
            $data['organizations']   = [
                'id'    => $model->organization->id,
                'title' => $model->organization->title
            ];
        }
        return $data;
    }

}