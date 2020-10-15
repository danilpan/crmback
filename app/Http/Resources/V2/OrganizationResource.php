<?php
namespace App\Http\Resources\V2;

class OrganizationResource extends Resource
{
    public function toArray($request)
    {
        $data   = [
            'id'                => $this->id,
            'title'             => $this->title,
            'permission_id'     => $this->permission_id,
            'parent_id'         => $this->parent_id,
            'role_id'           => $this->role_id,
            'is_company'        => $this->is_company,
            'key'               => $this->key,
            'api_key'           => $this->api_key
        ];

        if($this->path) {
            $data['path']   = self::collection($this->path);
        }

        return $data;
    }
}