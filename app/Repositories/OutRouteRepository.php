<?php
namespace App\Repositories;

use App\Models\OutRoute;

class OutRouteRepository extends Repository
{
    public function model()
    {
        return OutRoute::class;
    }
    
    public function getMappings()
    {
        $mappings   = [
            'id'    => [
                'type'  => 'integer',
            ],
        ];
        
        return $mappings;
    }
    
    public function prepareSearchData($model)
    {
        $data = [
            'id' => $model->id,
            'name' => $model->name,
            'comment' => $model->comment,
            'mask' => $model->mask,
            'replace_count' => $model->replace_count,
            'prefix' => $model->prefix,
            'trunks1' => $model->trunks1,
            'trunks2' => $model->trunks2,
            'trunks_p2' => $model->trunks_p2,
            'trunks_p1' => $model->trunks_p1,
            'ats_group_id' => $model->ats_group_id,
            'provider_id' => $model->provider_id,
            'is_work' => $model->is_work,
        ];
        
        if ($data['provider_id'] != null) {
            $data['provider_name'] = $model->provider->name;
        }
    
        return $data;
    }
}