<?php

namespace App\Repositories;


use App\Models\ProductImage;

class ProductImageRepository extends Repository
{
    public function model()
    {
        return ProductImage::class;
    }

    public function getMappings()
    {
        $mappings = [
            'id'        => [
                'type'  => 'integer'
            ],
            'product_id'=> [
                'type'  => 'integer'
            ],
            'image'     => [
                'type'  => 'keyword'
            ],
            'is_main'   => [
                'type'  => 'integer'
            ],
        ];
        return $mappings;
    }

    public function prepareSearchData($model)
    {
        $data = [
            'id'         => $model->id,
            'product_id' => $model->product_id,
            'image'      => $model->image,
            'is_main'    => $model->is_main,
        ];

        return $data;
    }
}