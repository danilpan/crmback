<?php
namespace App\Repositories;

use App\Models\AtsQueue;

class AtsQueueRepository extends Repository
{
    public function model()
    {
        return AtsQueue::class;
    }
    
    public function getMappings()
    {
        $mappings   = [
            'id'    => [
                'type'  => 'integer',
            ],
            'organization_id'    => [
                'type'  => 'integer',
            ],
            'organizations' => [
                'type' => 'nested',
                'include_in_parent' => true,
                'properties' => [
                    'id' => [
                        'type' => 'integer'
                    ]
                ]
            ]
        ];
        
        return $mappings;
    }
    
    public function prepareSearchData($model)
    {
        $data = [
            'id'              => $model->id,
            'type'            => $model->type,
            'name'            => $model->name,
            'comment'         => $model->comment,
            'steps1'          => $model->steps1,
            'steps2'          => $model->steps2,
            'off_time1'       => $model->off_time1,
            'off_time2'       => $model->off_time2,
            'how_call'        => $model->how_call,
            'strategy'        => $model->strategy,
            'check_wbt'       => $model->check_wbt,
            'unload_id'       => $model->unload_id,
            'organization_id' => $model->organization_id,
            'ats_group_id'    => $model->ats_group_id,
            'ats_group_name'  => $model->atsGroup->name,
            'caller_ids'      => $model->caller_ids,
            'operators'       => $model->operators,
            'is_work'         => $model->is_work,
            'organizations'   => [],
        ];
    
        if ($model->organizations) {
            foreach ($model->organizations as $organization) {
                $data['organizations'][] = [
                    'id' => $organization->id,
                    'parent_id' => $organization->parent_id
                ];
            }
        }
        return $data;
    }
}