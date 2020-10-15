<?php
namespace App\Repositories;

use App\Models\OrderSender;

class OrderSenderRepository extends Repository
{
    public function model()
    {
        return OrderSender::class;
    }

    public function getSearchRelations()
    {
        return [
            'organization'
        ];
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
            'is_work'    => [
                'type'  => 'boolean'
            ],
            'phone'    => [
                'type'  => 'keyword'
            ],
            'iin'  => [
                'type'  => 'keyword'
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
        ];

        return $mappings;
    }

    public function prepareSearchData($model)
    {
        $data = [
            'id'    =>  $model->id,
            'organization_id'   =>  $model->organization_id,
            'name'              =>  $model->name,
            'iin'               =>  $model->iin,
            'phone'             =>  $model->phone,
            'is_work'           =>  $model->is_work
        ];
        if($model->organization){
            $data['organizations'] = [
                'id' => $model->organization->id,
                'title' => $model->organization->title
            ];
        }

        return $data;
    }
}