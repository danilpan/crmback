<?php
namespace App\Repositories;

use App\Models\Product;

class ProductsRepository extends Repository
{
    public function model()
    {
        return Product::class;
    }

    public function getMappings()
    {
        $mappings   = [
            'id'    => [
                'type'  => 'integer'
            ],
            'article'    => [
                'type'  => 'keyword'
            ],
            'name'    => [
                'type'  => 'keyword',
                'normalizer' => 'normalizer_keyword'
            ],
            'weight'    => [
                'type'  => 'keyword'
            ],
            'desc'    => [
                'type'  => 'keyword'
            ],
            'is_work_title'    => [
                'type'  => 'keyword'
            ],
            'is_work'    => [
                'type'  => 'keyword'
            ],
            'is_kit'  => [
                'type'  => 'keyword'
            ],
            'related_goods' =>[
                'properties'    => [
                    'id'    => [
                        'type'  => 'keyword'
                    ],
                    'name' => [
                        'type'      => 'keyword'
                    ]
                ]
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
            'product_category'  => [
                'properties'    => [
                    'id'    => [
                        'type'  => 'keyword'
                    ],
                    'name' => [
                        'type'      => 'keyword'
                    ]
                ]
            ],
            'product_images' => [
                'properties' => [
                    'id'  => [
                        'type' => 'integer'
                    ],
                    'product_id' => [
                        'type' => 'integer'
                    ],
                    'image'=> [
                        'type' => 'keyword'
                    ],
                    'is_main'  => [
                        'type' => 'keyword'
                    ]
                ]
            ],
            'geo'  => [
                'type'                  => 'nested',
                'include_in_parent'     => true,
                'properties'    => [
                    'id'    => [
                        'type'  => 'integer'
                    ],
                    'code' => [
                        'type'      => 'keyword'
                    ],
                    'name_en' => [
                        'type'      => 'keyword',
                        'normalizer' => 'normalizer_keyword'
                    ],
                    'name_ru' => [
                        'type'      => 'keyword',
                        'normalizer' => 'normalizer_keyword'
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
            'article'       => $model->article,
            'name'          => $model->name,
            'price_cost'    => $model->price_cost,
            'organization_id' => $model->organization_id,
            'is_work'       => $model->is_work,
            'weight'        => $model->weight,
            'desc'          => $model->desc,
            'category_id'   => $model->category_id,
            'is_kit'        => $model->is_kit,
            'related_goods' => $model->related_goods
        ];

        if (!empty($model->projects)) {
            $data['projects'] = [];
            foreach ($model->projects as $project) {
                $data['projects'][] = [
                    'id' => $project->id,
                ];
            }
        }

        if($model->organization) {
            $data['organizations']   = [
                'id'    => $model->organization->id,
                'title' => $model->organization->title
            ];
        }

        $data['is_work_title']   = $model->is_work ? "Да" : "Нет";

        if($model->category) {
            $data['product_category']   = [
                'id'    => $model->category->id,
                'name' => $model->category->name
            ];
        }

        if(!empty($model->images)) {
            $data['product_images'] = [];
            foreach($model->images as $image){
                $data['product_images'][] = [
                    'id' => $image->id,
                    'product_id' => $image->product_id,
                    'image' => $image->image,
                    'is_main' => $image->is_main
                ];
            }
        }

        if(!empty($model->geo)){
            $data['geo'] = [];
            foreach ($model->geo as $geo) {
                $data['geo'][] = [
                    'id'      => $geo->id,
                    'code'    => $geo->code,
                    'name_en' => $geo->name_en,
                    'name_ru' => $geo->name_ru
                ];
            }
        }

        return $data;
    }

    public function getQueryFields()
    {
        $fields = [
            [
                'field' => 'article',
                'type'  => 'terms'
            ],
            [
                'field' => 'name',
                'type'  => 'wildcard'
            ]
        ];

        return $fields;
    }

}
