<?php

namespace App\Http\Resources\V2;


class AtsResource extends Resource
{
    public function toArray($request)
    {
        $data   = [
            'id'    => $this->id,
            'ip' => $this->ip,
            'key' => $this->key,
            'name' => $this->name,
            'description' => $this->description,
            'is_work'  => $this->is_work,
            'is_default'  => $this->is_default
        ];

        return $data;
    }
}
