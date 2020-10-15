<?php
namespace App\Repositories;

use App\Models\ProjectCategoryKc;


class ProjectCategoryKcRepository extends Repository 
{

    public function model()
    {
        return ProjectCategoryKc::class;
    }
    
    public function getMappings()
    {

        $contextMapping = $this->getSuggestContextMapping();
        $mappings   = [
            'id'    => [
                'type'  => 'keyword'
            ],
            'title' => [
                'type'  => 'keyword'
            ],
	        'is_work' => [
                'type'  => 'boolean'
            ]
        ];

        return $mappings;
        
    }

    public function prepareSearchData($model)
    {
        $data   = [
	        'id'                => $model->id,
            'title'             => $model->title,
            'is_work'           => $model->is_work
        ];

       return $data;
    }



    public function getQueryFields()
    {
         $fields = [
             [	
                'field' => 'title',
                'type'  => 'wildcard'
             ]
       
         ];

        return $fields;
    }





}
