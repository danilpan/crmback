<?php
namespace App\Repositories;

use App\Models\ProductCategory;

class ProductCategoryRepository extends Repository
{
    public function model()
    {
        return ProductCategory::class;
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
            'name'    => [
                'type'  => 'keyword'
            ]
        ];

        return $mappings;
    }

    public function prepareSearchData($model)
    {
        $data = [
            'id'            => $model->id,
            'name'          => $model->name,
            'is_work'       => $model->is_work,
        ];


        if($model->organization) {
            $data['organizations'] = [
                'id'        => $model->organization->id,
                'title'     => $model->organization->title
            ];
        }

        return $data;
    }
}