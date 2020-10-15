<?php

namespace App\Http\Resources\V2;


class UnloadResource extends Resource
{
    public function toArray($request)
    {
        $data   = [
            'key'             => $this->id,
            'id'              => $this->id,
            'name'            => $this->name,
            'comment'         => $this->comment,
            'organization_id' => $this->organization_id,
            'config'          => $this->config,
            'api_key'         => $this->api_key,
            'is_work'         => $this->is_work
        ];

        return $data;
    }
}