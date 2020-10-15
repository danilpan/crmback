<?php

namespace App\Http\Resources\V2;


class EntityResource extends Resource
{
    public function toArray($request)
    {
        $data   = [
            'id'    => $this->id,
            'name' => $this->name
        ];

        return $data;
    }
}
