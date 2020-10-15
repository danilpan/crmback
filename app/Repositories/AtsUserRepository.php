<?php
namespace App\Repositories;

use App\Models\AtsUser;

class AtsUserRepository extends Repository
{
    public function model()
    {
        return AtsUser::class;
    }

    public function getMappings()
    {            
        $mappings   = [
            'id'    => [
                'type'  => 'integer'
            ],
            'port'    => [
                'type'  => 'integer'
            ],
            'passwd'    => [
                'type'  => 'keyword'                
            ],
            'login'    => [
                'type'  => 'keyword'                
            ],
            'max_channels'    => [
                'type'  => 'integer'
            ],
            'is_work'    => [
                'type'  => 'boolean'
            ],
            'type'    => [
                'type'  => 'keyword'
            ],
            'ats_group'    => [
                'type'  => 'keyword'
            ],            
            'caller_ids'    => [
                'type'  => 'keyword'
            ],            
            'user_name'    => [
                'type'  => 'keyword',
                'normalizer' => 'normalizer_keyword'
            ],
            'user_id'    => [
                'type'  => 'integer'
            ]
        ];

        return $mappings;
    }
    
    public function prepareSearchData($model)
    {
        $data   = [
            'id'    => $model->id,
            'port' => $model->port,
            'passwd' => $model->passwd,
            'login' => $model->login,
            'max_channels' => $model->max_channels,
            'is_work' => $model->is_work,
            'type' => $model->type,
            'caller_ids' => $model->sipCallerIds,
            'ats_group' => $model->atsGroup->name,
            'user_id'              => ($model->user)?$model->user->id:0,
            'user_name'            => ($model->user)?$model->user->last_name." ".$model->user->first_name." ".$model->user->middle_name.'('.$model->user->organization->title.')':'Отсутствует'
        ];

        if($model->sipCallerIds){
            $caller_ids  = [];
            foreach ($model->sipCallerIds as $caller_id) {
                $caller_ids[] = $caller_id['caller_id'];
            }
            $data['caller_ids'] = implode(", ", $caller_ids);
        }
        
        if($model->atsGroup->organizations) {
            $data['organizations']  = [];
            foreach ($model->atsGroup->organizations as $organization) {
                $data['organizations'][]    = [
                    'id' => $organization->id,
                    'parent_id' => $organization->parent_id
                ];
            }
        }
        
        return $data;
    }
}