<?php

namespace App\Http\Resources\V2;


class EntityParamResource extends Resource
{
    public function toArray($request)
    {
        $data   = [
            'id'    => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'entity_id' => $this->entity_id,
            'parent_id' => $this->parent_id,
            'parameter' => $this->parameter
        ];

        return $data;
    }
}
