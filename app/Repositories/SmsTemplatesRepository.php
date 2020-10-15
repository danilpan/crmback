<?php
namespace App\Repositories;

use App\Models\SmsTemplate;

class SmsTemplatesRepository extends Repository
{
    public function model()
    {
        return SmsTemplate::class;
    }

    public function getSearchRelations()
    {
        return [
            'organizations'
        ];
    }

    public function getMappings()
    {
        $mappings = [
            'id' => [
                'type' => 'integer'
            ],
            'name' => [
                'type' => 'keyword'
            ],
            'sms_text' =>[
                'type' => 'text'
            ],
            'is_work' => [
                'type' => 'boolean'
            ],
            'organizations'  => [
                'type' => 'nested',
                'include_in_parent' => true,
                'properties'    => [
                    'id'    => [
                        'type'  => 'integer'
                    ],
                    'title' => [
                        'type'  => 'keyword'
                    ]
                ]
            ],
        ];

        return $mappings;
    }

    public function prepareSearchData($model)
    {
        $data = [
            'id'              => $model->id,
            'name'            => $model->name,
            'sms_text'        => $model->sms_text,
            'is_work'         => $model->is_work
        ];

        if (!empty($model->organizations)) {
            $data['organizations']  = [];
            foreach ($model->organizations as $organization) {
                $data['organizations'][] = [
                    'id' => $organization->id,
                    'title' => $organization->title
                ];
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
                'field' => 'name',
                'type'  => 'terms'
            ],
            [
                'field' => 'is_work',
                'type'  => 'terms'
            ]
        ];

        return $fields;
    }
}
