<?php
namespace App\Repositories;

use App\Models\BlackList;

class BlackListRepository extends Repository
{
    public function model()
    {
        return BlackList::class;
    }

    public function getQueryFields()
    {
        
        $fields = [            
            [
                'field' => 'phone',
                'type'  => 'wildcard'
            ]
        ];

        return $fields;

    }

    public function getMappings()
    {
        
        $mappings   = [      
            'id'    => [
                'type'  => 'integer'
            ],      
            'phone' => [
                'type'  => 'text'
            ],
            'user_id'   => [
                'type'  => 'integer'
            ],
        ];

        return $mappings;
    }

    public function prepareSearchData($model)
    {

        $data   = [            
            'id'                        => $model->id,
            'phone'                     => $model->phone,
            'user_id'                   => $model->user_id,
        ];

        return $data;
    }

}