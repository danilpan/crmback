<?php
namespace App\Repositories;

use App\Models\DeliveryType;

class DeliveryTypesRepository extends Repository 
{
    public function model()
    {
        return DeliveryType::class;
    }

    public function getMappings()
    {
        $mappings   = [
            'id'    => [
                'type'  => 'integer'
            ],
            'organization_id'    => [
                'type'  => 'keyword'
            ],
            'name'    => [
                'type'  => 'keyword'
            ],
            'price'    => [
                'type'  => 'keyword'
            ],
            'surplus_percent' => [
                'type' => 'keyword'
            ],
            'is_work'    => [
                'type'  => 'boolean'
            ],
            'is_show'    => [
                'type'  => 'boolean'
            ],
            'organizations'  => [
                'properties'    => [
                    'id'    => [
                        'type'  => 'integer'
                    ],
                    'title' => [
                        'type'      => 'keyword'
                    ]
                ]
            ],
            'projects' => [
                'type' => 'nested',
                'include_in_parent' => true,
                'properties' => [
                    'delivery_type_id' => [
                        'type' => 'integer'
                    ],
                    'project_id' => [
                        'type' => 'integer'
                    ],
                    'geo_id' => [
                        'type' => 'integer'
                    ]
                ]
            ]
        ];

        return $mappings;
    }

    public function prepareSearchData($model)
    {
        $data   = [
            'id'                => $model->id,
            'organization_id'   => $model->organization_id,
            'name'              => $model->name,
            'price'             => $model->price,
            'surplus_percent'   => $model->surplus_percent,
            'is_work'           => $model->is_work,
            'is_show'           => $model->is_show
        ]; 
        
        if($model->organization) {
            $data['organizations']   = [
                'id'    => $model->organization->id,
                'title' => $model->organization->title
            ];
        }
        
        $data['projects'] = [];
        if ($model->projects) {
            foreach ($model->projects as $key => $value) {
                $data['projects'][] = $value->pivot;
            }
        }

        return $data;
    }

    public function getQueryFields()
    {
        $fields = [
        [  
           'field' => 'id',
           'type'  => 'terms'
        ],
        [  
           'field' => 'organization_id',
           'type'  => 'terms'
        ],
        [  
           'field' => 'name',
            'type'  => 'wildcard'
        ],
        [  
           'field' => 'price',
            'type'  => 'terms'
        ],
        [  
           'field' => 'is_work',
            'type'  => 'terms'
        ],
        [  
           'field' => 'is_show',
            'type'  => 'terms'
        ]

        ];

        return $fields;
    }
    
}  