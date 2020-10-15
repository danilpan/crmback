<?php

namespace App\Http\Resources\V2;


class RoleGroupResource extends Resource
{
    public function toArray($request)
    {
        $data   = [
            'id'    => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'creator_organization_id' => $this->creator_organization_id
        ];

        return $data;
    }
}
