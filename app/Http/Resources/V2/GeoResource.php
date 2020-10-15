<?php

namespace App\Http\Resources\V2;


class GeoResource extends Resource
{
    public function toArray($request)
    {
        $data   = [
            'id'    => $this->id,
            'name_ru' => $this->name_ru,
            'name_en' => $this->name_en,
            'mask' => $this->mask,
            'code' => $this->code,
            'key'  => $this->key,
            'summary' => $this->summary,
            'items' =>  $this->items
        ];

        return $data;
    }
}
