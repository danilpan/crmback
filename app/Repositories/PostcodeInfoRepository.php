<?php
namespace App\Repositories;

use App\Models\PostcodeInfo;

class PostcodeInfoRepository extends Repository
{
    public function model()
    {
        return PostcodeInfo::class;
    }

    public function getMappings()
    {        

        $mappings   = [
            'id'    => [
                'type'  => 'integer'
            ],
            'postcode' =>[
                'type'  => 'keyword'
            ],
            'delivery_type_id' =>[
                'type'  => 'integer'
            ],
            'comment' => [
                'type'  => 'text'            
            ],
            'time'   => [
                'type'  => 'integer'               
            ],
            'price'   => [
                'type'  => 'integer'               
            ]
            ];

        return $mappings;
    }

    public function prepareSearchData($model)
    {
        $data   = [
	        'id'                => $model->id,
            'postcode'	    => $model->postcode,
            'delivery_type_id'              => $model->delivery_type_id,
            'comment'       		=> $model->comment,
            'time'       		=> $model->time,
            'price'       		=> $model->price,
        ];         

        return $data;
    }

   public function getQueryFields()
   {
	 $fields = [
	 [	
		'field' => 'postcode',
		'type'  => 'wildcard'
  	 ],
	 [	
		'field' => 'delivery_type_id',
		'type'  => 'wildcard'
  	 ]

	];

	return $fields;
   }

}