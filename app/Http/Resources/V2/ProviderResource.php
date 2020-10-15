<?php

namespace App\Http\Resources\V2;


class ProviderResource extends Resource
{
    public function toArray($request)
    {
        $data   = [
            'id'    => $this->id,
            'name' => $this->name,
            'img' => $this->img,
            'comment' => $this->comment,
        ];

        return $data;
    }
}
