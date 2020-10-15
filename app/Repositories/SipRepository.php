<?php
namespace App\Repositories;

use App\Models\Sip;

class SipRepository extends Repository
{
    public function model()
    {
        return Sip::class;
    }

    public function getMappings()
    {
        $mappings   = [
            'id'    => [
                'type'  => 'integer'
            ],
            'host'    => [
                'type'  => 'keyword'
            ],
            'port'    => [
                'type'  => 'integer'
            ],
            'login'    => [
                'type'  => 'keyword'
            ],
            'passwd'    => [
                'type'  => 'text'
            ],
            'max_channels'    => [
                'type'  => 'integer'
            ],
            'template'    => [
                'type'  => 'keyword'
            ],
            'connect_type'    => [
                'type'  => 'keyword'
            ],
            'ats_group_id'    => [
                'type'  => 'integer'
            ],
            'is_work'    => [
                'type'  => 'boolean'
            ],
            'ats_group'  => [
                'properties'    => [
                    'id'    => [
                        'type'  => 'integer'
                    ],
                    'name' => [
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
            'id'            => $model->id,
            'host'          => $model->host,
            'description'   => $model->description,
            'port'          => $model->port,
            'passwd'        => $model->passwd,
            'login'         => $model->login,
            'max_channels'  => $model->max_channels,
            'template'      => $model->template,
            'connect_type'  => $model->connect_type,
            'ats_group_id'  => $model->ats_group_id,
            'is_work'       => $model->is_work
        ];
    
        if($model->atsGroup) {
            $data['ats_group'] = [
                'id'    => $model->atsGroup->id,
                'name' => $model->atsGroup->name,
                'organizations' => $model->atsGroup->organizations
            ];
        }
    
        return $data;
    }
}