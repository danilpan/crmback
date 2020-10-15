<?php

namespace App\Http\Resources\V2;


class ProjectCategoryKcResource extends Resource
{
    public function toArray($request)
    {
        $data   = [
            'id' =>	$this->id,
			'title' =>	$this->title,
            'is_work' =>	$this->is_work,
            'key' =>	$this->key
        ];

        return $data;
    }
}
