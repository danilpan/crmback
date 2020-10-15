<?php

namespace App\Http\Resources\V2;


class PostcodeInfoResource extends Resource
{
    public function toArray($request)
    {
        $data   = [
            'id'    => $this->id,
            'postcode' => $this->postcode,
            'delivery_type_id' => $this->delivery_type_id,
            'comment' => $this->comment,
            'time' => $this->time,
            'price' => $this->price
        ];

        return $data;
    }
}
