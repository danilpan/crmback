<?php

namespace App\Http\Resources\V2;


class TrafficResource extends Resource
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
