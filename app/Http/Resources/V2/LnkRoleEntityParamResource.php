<?php

namespace App\Http\Resources\V2;


class LnkRoleEntityParamResource extends Resource
{
    public function toArray($request)
    {
        $data   = [
            'id'    => $this->id,
            'role_id' => $this->role_id,
            'entity_param_Id' => $this->entity_param_Id,
            'entity_id' => $this->entity_id
        ];

        return $data;
    }
}
