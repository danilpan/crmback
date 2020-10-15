<?php
namespace App\Repositories;

use App\Models\Attempt;

class AttemptsRepository extends Repository
{
    public function model()
    {
        return Attempt::class;
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
            'source'    => [
                'type'  => 'keyword',
                'normalizer' => 'normalizer_keyword'
            ],
            'body'    => [
                'type'  => 'keyword',
                'normalizer' => 'normalizer_keyword'
            ],
            'image'    => [
                'type'  => 'keyword',
            ],
            'created_at'    => [
                'type'  => 'date',
                'format'    => 'yyyy/MM/dd HH:mm:ss||yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'
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
            ],
        ];

        return $mappings;
    }

    public function prepareSearchData($model)
    {
        $data = $model->toArray();

        if($model->organization) {
            $data['organizations']   = [
                'id'    => $model->organization->id,
                'title' => $model->organization->title
            ];
        }

        return $data;
    }
}