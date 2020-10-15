<?php
/**
 * Created by PhpStorm.
 * User: timur
 * Date: 14.02.19
 * Time: 14:23
 */

namespace App\Repositories;


use App\Models\OrderAdvertSource;

class OrderAdvertSourceRepository extends Repository
{
    public function model(){
        return OrderAdvertSource::class;
    }

    public function getSearchRelations()
    {
        return [
            'order'
        ];
    }

    public function getMappings(){
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
            'id' => $model->id,
            'name' => $model->name,
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
            ],
        ];

        return $fields;
    }
}