<?php
namespace App\Repositories;

use App\Models\Organization;

class OrganizationsRepository extends Repository
{
    public function getAncestors(Organization $organization)
    {
        return $organization->ancestors()->get();
    }

    public function model()
    {
        return Organization::class;
    }

    public function prepareSearchData($model)
    {
        $data               = $model->toArray();
        $data['parent_id']  = $data['parent_id'] ?? 0;
    
        $data['organizations']   = [
            'id'    => $model->id,
            'title' => $model->title
        ];

        return $data;
    }

    public function getMappings()
    {
        $mappings   = [
            'id'    => [
                'type'  => 'integer'
            ]
        ];

        return $mappings;
    }

}