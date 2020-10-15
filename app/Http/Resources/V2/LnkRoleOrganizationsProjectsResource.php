<?php

namespace App\Http\Resources\V2;


class LnkRoleOrganizationsProjectsResource extends Resource
{
    public function toArray($request)
    {
        $data   = [
            'id'    => $this->id,
            'role_id' => $this->role_id,
            'organizations' => $this->organizations,
            'projects' => $this->projects,
            'is_deduct_organization' => $this->is_deduct_organization,
            'is_deduct_project' => $this->is_deduct_project
        ];

        return $data;
    }
}
