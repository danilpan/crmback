<?php

namespace App\Http\Resources\V2;


class RoleResource extends Resource
{
    public function toArray($request)
    {
        $data   = [
            'id'    => $this->id,
            'name' => $this->name,
            'group_id' => $this->group_id,
            'is_superuser' => $this->is_superuser,
            'description' => $this->description,
            'creator_organization_id' => $this->creator_organization_id,
            'entity_params' => EntityParamResource::collection($this->whenLoaded('entity_param')),
        ];

        return $data;
    }
}
