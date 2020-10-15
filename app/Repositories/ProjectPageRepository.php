<?php
namespace App\Repositories;

use App\Models\ProjectPage;

class ProjectPageRepository extends Repository
{
    public function model()
    {
        return ProjectPage::class;
    }

    public function getMappings()
    {        

        $mappings   = [
            'id'    => [
                'type'  => 'integer'
            ],
            'project_id' =>[
                'type'  => 'keyword'
            ],
            'name' => [
                'type'  => 'keyword',
                'normalizer' => 'normalizer_keyword'
            ],
            'link'   => [
                'type'  => 'text',
                'analyzer'  => 'link'
            ],
            'organizations'   => [
                'properties'    => [
                    'id'    => [
                        'type'  => 'keyword'
                    ],
                    'title' => [
                        'type'      => 'keyword'
                    ]
                ]
            ],
            ];

        return $mappings;
    }

    public function prepareSearchData($model)
    {
        $data   = [
	        'id'                => $model->id,
            'project_id'	    => $model->project_id,
            'name'              => $model->name." ".$model->link,
            'link'       		=> $model->link
        ];         

        if($model->organization) {
            $data['organizations']   = [
                'id'    => $model->organization->id,
                'title' => $model->organization->title
            ];
        }else{
            $data['organizations']   = [
                'id'    => null,
                'title' => null
            ];
        }

        return $data;
    }

   public function getQueryFields()
   {
	 $fields = [
	 [	
		'field' => 'name',
		'type'  => 'wildcard'
  	 ],
	 [	
		'field' => 'link',
		'type'  => 'wildcard'
  	 ],
	 [	
	 	'field' => 'project_id',
	 	'type'  => 'terms'
	 ]

	];

	return $fields;
   }

  
}