<?php
namespace App\Repositories;

use App\Models\Status;

class StatusesRepository extends Repository
{
    public function model()
    {
        return Status::class;
    }

    public function getMappings()
    {
        $mappings   = [
            'id'    => [
                'type'  => 'keyword'
            ],
            'parent_id' => [
                'type'  => 'integer'
            ],            
            'organization_id'   => [
                'type'  => 'integer'
            ],
            'name'   => [
                'type'  => 'keyword'
            ],
            'title'   => [
                'type'  => 'keyword'
            ],
            'desc'   => [
                'type'  => 'text'
            ],
            'is_work'   => [
                'type'  => 'integer'
            ],
            'type'   => [
                'type'  => 'integer'
            ],
            'color'   => [
                'type'  => 'text'
            ],
            'sort'   => [
                'type'  => 'integer'
            ]
        ];

        return $mappings;
    }

    public function prepareSearchData($model){
        $data   = [
            'id'                 => $model->id,
            'parent_id'          => $model->parent_id,
            'organization_id'    => $model->organization_id,
            'name'               => $model->name,
            'desc'               => $model->desc,
            'is_work'            => $model->is_work,
            'type'               => $model->type,
            'color'              => $model->color,
            'sort'               => $model->sort,
        ];
        return $data;
    }

}