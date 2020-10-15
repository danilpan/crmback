<?php
namespace App\Repositories\Optovichok;

use App\Models\Optovichok\Client;
use App\Repositories\Repository;

class ClientRepository extends Repository
{
    public function model()
    {
        return Client::class;
    }

    public function getMappings()
    {
        $mappings   = [
            'id'    => [
                'type'  => 'integer'
            ],
            'client_name'    => [
                'type'  => 'keyword'
            ],
            'phone' => [
                'type' => 'keyword'
            ],
            'iin' => [
                'type' => 'keyword'
            ],
            'advert_source_id' => [
                'type' => 'integer'
            ],
            'advert_source'   => [
                'properties'    => [
                    'id'  => [
                        'type'  => 'integer'
                    ],
                    'title' => [
                        'type'  => 'keyword'
                    ]
                ]
            ],
            'organizations'   => [
                'properties'    => [
                    'id'  => [
                        'type'  => 'integer'
                    ],
                    'title' => [
                        'type'  => 'keyword'
                    ]
                ]
            ]
        ];

        return $mappings;
    }

    public function prepareSearchData($model)
    {
        $data = $model->toArray();

        if($model->organization) {
            $data['organizations'] = [
                'id'    => $model->organization->id,
                'title' => $model->organization->title
            ];
        }

        return $data;
    }

    public function getQueryFields()
    {
        $fields = [
            [
                'field' => 'client_name',
                'type'  => 'wildcard'
            ],
        ];

        return $fields;
    }
}