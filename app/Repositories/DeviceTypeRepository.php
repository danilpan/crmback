<?php
/**
 * Created by PhpStorm.
 * User: timur
 * Date: 18.02.19
 * Time: 17:19
 */

namespace App\Repositories;


use App\Models\DeviceType;

class DeviceTypeRepository extends Repository
{
    public function model(){
        return DeviceType::class;
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
            'is_show' => [
                'type' => 'boolean'
            ]
        ];

        return $mappings;
    }

    public function prepareSearchData($model)
    {
        $data = [
            'id'      => $model->id,
            'name'    => $model->name,
            'is_show' => $model->is_show
        ];

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
                'field' => 'is_show',
                'type'  => 'terms'
            ]
        ];

        return $fields;
    }
}