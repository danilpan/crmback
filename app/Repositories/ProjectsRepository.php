<?php
namespace App\Repositories;

use App\Models\Project;

class ProjectsRepository extends Repository
{
    protected $suggestContexts  = [
        'filters'   => [
            ['organization_id']
        ]
    ];

    public function model()
    {
        return Project::class;
    }

    public function getSearchRelations()
    {
        return [
            'project_page'
        ];
    }

    public function getMappings()
    {
        $contextMapping = $this->getSuggestContextMapping();

        $mappings   = [
            'id'    => [
                'type'  => 'integer'
            ],
            'item_id' =>[
                'type'  => 'keyword'
            ],
            'title' => [
                'type'  => 'keyword',
                'normalizer' => 'normalizer_keyword'
                
            ],
            'description'   => [
                'type'  => 'keyword',
                'normalizer' => 'normalizer_keyword'
            ],
            'organization_id'   => [
                'type'  => 'integer'
            ],
            'name_for_client'   => [
                'type'  => 'text'
            ],
             'category_id'   => [
                'type'  => 'integer'
            ],
            'organizations'  => [
                'properties'    => [
                    'id'    => [
                        'type'  => 'keyword'
                    ],
                    'title' => [
                        'type'      => 'keyword',
                        'normalizer' => 'normalizer_keyword'
                    ]
                ]
            ],
            'project_page' => [
                'type'	=> 'nested',
                'include_in_parent' => true,
                    'properties' => [
                       'id'    => [
                            'type'  => 'keyword'
                        ],
                        'name' => [
                            'type' => 'keyword',
                            'normalizer' => 'normalizer_keyword'
                        ],
                        'import_id'    => [
                            'type'  => 'keyword'
                        ],
                        'link' => [
                            'type' => 'text',
                            'analyzer'  => 'link'
                        ],
                        'project_page_phone' =>[
                            'type' => 'nested',
                            'include_in_parent' => true,
                            'properties' => [
                                'id' => [
                                    'type' => 'keyword'
                                ],
                                'phone' => [
                                    'type' => 'text'
                                ]
                            ]
                        ]
                    ]
            ],
            'project_category'  => [
                'properties'    => [
                    'id'    => [
                        'type'  => 'keyword'
                    ],
                    'name' => [
                        'type'      => 'keyword'
                    ]
                ]
            ],
            'project_category_kc'  => [
                'properties'    => [
                    'id'    => [
                        'type'  => 'keyword'
                    ],
                    'title' => [
                        'type'      => 'keyword'
                    ]
                ]
            ],
            'project_category_kc_id' => [
                'type' => 'keyword'
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
            ],
            'replica' => [
                'type' => 'text'
            ],
            'operator_notes'   => [
                'type'  => 'text'
            ],
            'created_at'    => [
                'type'      => 'date',
                'format'    => 'yyyy/MM/dd HH:mm:ss||yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'
            ]
            // 'suggest'   => [
            //         'type'      => 'completion',
            //         'analyzer'  => 'standard',
            //         'contexts'  => $contextMapping
            //    ],
            
            ];

        return $mappings;
    }

    public function prepareSearchData($model)
    {
        $data   = [
	        'id'                    => $model->id,
            'item_id'   	        => $model->id,
            'title'                 => $model->title,
            'description'           => $model->description,
            'organization_id'       => $model->organization_id,
            'category_id'           => $model->category_id,
            'name_for_client'       => $model->name_for_client,
            'project_category_kc_id'=> $model->project_category_kc_id,
            'operator_notes'        => $model->operator_notes
            
        ];

        // if($model->title) {
        //     $input  = array_unique(explode(' ', $model->title));
        //     $input  = array_filter($input, function($p){
        //         return mb_strlen($p) > 1;
        //     });

        //     if(count($input)) {
        //         $data['suggest']    = [
        //             'input'     => array_values($input),
        //             'contexts'  => $this->getSuggestContextValues($model->toArray())
        //         ];
        //     }
        // }

        if($model->created_at) {
            $data['created_at'] = $model->created_at->format('Y-m-d H:i:s');
        }

    	if($model->project_page){
    		$data['project_page'] = [];
    		foreach($model->project_page as $page){
    			$data['project_page'][] = [
                   	'id'  => $page->id,
    				'name'	=> $page->name.' '.$page->link,
    				'link'	=> $page->link,
                    'phones' =>  $page->phones()->get(),
    				'import_id'	=> $page->import_id
    			];
    		}
    	}	

        if($model->organization) {
            $data['organizations']   = [
                'id'    => $model->organization->id,
                'title' => $model->organization->title
            ];
        }

        if($model->category) {
            $data['project_category']   = [
                'id'    => $model->category->id,
                'name' => $model->category->name
            ];
        }

        if($model->project_category_kc) {
            $data['project_category_kc']   = [
                'id'    => $model->project_category_kc->id,
                'title' => $model->project_category_kc->title
            ];
        }

        $data['geo']  = [];
        $geos = null;
        $geos = $model->geo()->get();
        if($geos != null) {
            foreach ($geos as $geo) {
                $data['geo'][] = [
                        'id'    => $geo['id'],
                        'code' => $geo['code'],
                        'name_en' => $geo['name_en'],
                        'name_ru' => $geo['name_ru']
                    ];
            }
        }

        return $data;
    }

   public function getQueryFields()
   {
	 $fields = [
	 [	
		'field' => 'item_id',
		'type'  => 'terms'
  	 ],
	 [	
		'field' => 'title',
		'type'  => 'wildcard'
  	 ],
	 [	
	 	'field' => 'description',
	 	'type'  => 'terms'
	 ],
     [  
        'field' => 'project_page.link',
        'type'  => 'wildcard'
     ],
	 [
		'field' => 'project_page.phones.phone',
		'type' => 'wildcard'
     ]
	];

	return $fields;
   }

}
