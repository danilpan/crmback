<?php

namespace App\Http\Resources\V2;


class ProductImageResource extends Resource
{
    public function toArray($request)
    {
        $data   = [
            'id'    => $this->id,
            'product_id'  => $this->product_id,
            'image' => $this->image,
            'is_main' => $this->is_main
        ];

        return $data;
    }
}