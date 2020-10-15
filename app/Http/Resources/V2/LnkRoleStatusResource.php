<?php

namespace App\Http\Resources\V2;


class LnkRoleStatusResource extends Resource
{
    public function toArray($request)
    {
        $data   = [
            'id'    => $this->id,
            'role_id' => $this->role_id,
            'status_id' => $this->status_id,
            'is_view' => $this->is_view,
            'is_can_set' => $this->is_can_set,
            'statuses' => StatusResource::collection($this->whenLoaded('statuses')),
            'roles' => $this->roles,
            'all_child_chosen' => $this->all_child_chosen,
            'has_child_chosen' => $this->has_child_chosen
        ];

        return $data;
    }
}
