<?php
namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Str;

class UsersRepository extends Repository
{
    public function model()
    {
        return User::class;
    }

    public function getSearchRelations()
    {
        return ['organization'];
    }

    public function getMappings()
    {
        $mappings   = [
            'id'    => [
                'type'  => 'integer'
            ],
            'name'    => [
                'type'  => 'keyword'
            ],
            'manager'    => [
                'type'      => 'keyword',
                'normalizer' => 'normalizer_keyword'
            ],
            'organizations'   => [
                'properties'    => [
                    'id'  => [
                        'type'  => 'integer'
                    ]
                ]
            ],
            'company'   => [
                'properties'    => [
                    'id'  => [
                        'type'  => 'integer'
                    ]
                ]
            ],
            'user_images' => [
                'properties' => [
                    'id'  => [
                        'type' => 'integer'
                    ],
                    'user_id' => [
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
            'telegram' =>[
                'type'  => 'keyword'
            ],
            'atsUsers' => [
                'type' => 'nested',
                'include_in_parent' => true,
                'properties' => [
                    'login' => [
                        'type' => 'text'
                    ]
                ]
            ],
            'image_type_id' => [
                'type' => 'keyword'
            ],
            'plates'    => [
                'type'  => 'keyword'
            ],
        ];

        return $mappings;
    }

    public function prepareSearchData($model)
    {
        $data = $model->toArray();

        if(!isset($data['organization'])) {
            $data['organizations']   = [
                'id'    => 0
            ];
        }else{
            $data['organizations']   = $data['organization'];
        }
        
        if(isset($model->atsUsers)) {
            $data['atsUsers'] = [];
            foreach ($model->atsUsers as $atsUser) {
                $data['atsUsers'][] = $atsUser;
            }
        }else{
            $data['atsUsers'] = [];
        }

        if(!isset($data['company'])) {
            $data['company']   = [
                'id'    => 0
            ];
        }

        $data['name'] = $data['last_name']." ".$data['first_name']." ".$data['middle_name'];
        $data['manager'] = Str::title($data['phone_office']." | ".$data['last_name']." ".$data['first_name']." ".$data['middle_name']);

        return $data;
    }

    public function getQueryFields()
    {
      $fields = [
      [	
         'field' => 'first_name',
         'type'  => 'wildcard'
        ],
      [	
          'field' => 'last_name',
          'type'  => 'wildcard'
      ],
      [  
         'field' => 'middle_name',
         'type'  => 'wildcard'
      ],
      [  
         'field' => 'login',
         'type'  => 'wildcard'
      ],
      [  
         'field' => 'atsUsers.login',
         'type'  => 'wildcard'
      ],
     ];
 
     return $fields;
    }

}