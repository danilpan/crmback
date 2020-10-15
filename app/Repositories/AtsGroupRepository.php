<?php
namespace App\Repositories;

use App\Models\AtsGroup;

class AtsGroupRepository extends Repository
{
    public function model()
    {
        return AtsGroup::class;
    }
    
    public function prepareSearchData($model)
    {
        $data   = [
            'id' => $model->id,
            'ats_id' => $model->ats_id,
            'name' => $model->name,
            'description' => $model->description,
            'is_work' => $model->is_work
        ];
        
        if($model->organizations) {
            $data['organizations']  = [];
            foreach ($model->organizations as $organization) {
                $data['organizations'][]    = [
                    'id' => $organization->id,
                    'parent_id' => $organization->parent_id
                ];
            }
        }
        
        if ($model->ats) {
            $data['ats_name'] = $model->ats->name;
        } else {
            $data['ats_name'] = '';
        }
        
        return $data;
    }
}