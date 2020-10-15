<?php
namespace App\Repositories;

use App\Models\Geo;

class GeoRepository extends Repository
{
    public function model()
    {
        return Geo::class;
    }

    public function getMappings()
    {
        $mappings   = [
            'id'    => [
                'type'  => 'integer'
            ],
            'code'    => [
                'type'  => 'keyword',
                'normalizer' => 'normalizer_keyword'
            ],
            'name_en'    => [
                'type'  => 'keyword',
                'normalizer' => 'normalizer_keyword'
            ],
            'name_ru'    => [
                'type'  => 'keyword',
                'normalizer' => 'normalizer_keyword'
            ],
            'mask'    => [
                'type'  => 'keyword'
            ]
        ];

        return $mappings;
    }

    public function prepareSearchData($model)
    {
        $data = [
            'id'            => $model->id,
            'code'       	=> $model->code,
            'name_en'       => $model->name_en,
            'name_ru'       => $model->name_ru,
            'mask'    		=> $model->mask            
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
            'field' => 'code',
            'type'  => 'terms'
         ],
         [  
            'field' => 'name_en',
            'type'  => 'terms'
         ],
         [  
            'field' => 'name_ru',
            'type'  => 'terms'
         ],
         [  
            'field' => 'mask',
            'type'  => 'terms'
         ]
        ];

        return $fields;
        }

  
}