<?php

namespace App\Http\Resources\V2;


class ProjectCategoryResource extends Resource
{
    public function toArray($request)
    {
        $data   = [
            'id' =>	$this->id,
            'organization_id' =>	$this->organization_id,
			'name' =>	$this->name,
            'is_work' =>	$this->is_work,
            'key'   => $this->key
        ];

        return $data;
    }
}
