<?php
namespace App\Repositories;

use App\Models\ProjectCategory;


class ProjectCategoryRepository extends Repository 
{

    protected $suggestContexts  = [
        'filters'   => [
            ['organization_id']
        ]
    ];

    public function model()
    {
        return ProjectCategory::class;
    }
    
    public function getMappings()
    {

        $contextMapping = $this->getSuggestContextMapping();
        $mappings   = [
            'id'    => [
                'type'  => 'keyword'
            ],
            'ornagization_id' =>[
                'type'  => 'keyword'
            ],
            'name' => [
                'type'  => 'text'
            ],
	        'is_work' => [
                'type'  => 'boolean'
            ],
             'suggest'   => [
                'type'      => 'completion',
                'analyzer'  => 'standard',
                'contexts'  => $contextMapping
             ],
        ];

        return $mappings;
        
    }

    public function prepareSearchData($model)
    {
        $data   = [
	        'id'                => $model->id,
            'organization_id'   => $model->organization_id,
            'name'              => $model->name,
            'is_work'           => $model->is_work
        ];
 
        if($model->name) {
            $input  = array_unique(explode(' ', $model->name));
            $input  = array_filter($input, function($p){
                return mb_strlen($p) > 1;
            });

            if(count($input)) {
                $data['suggest']    = [
                    'input'     => array_values($input),
                    'contexts'  => $this->getSuggestContextValues($model->toArray())
                ];
            }

            if($model->organization) {
            $data['organizations'] = [
                'id'        => $model->organization->id,
                'title'     => $model->organization->title
            ];
        }

        }

       return $data;
    }



    public function getQueryFields()
    {
         $fields = [
             [	
                'field' => 'name',
                'type'  => 'wildcard'
             ]
       
         ];

        return $fields;
    }





}
