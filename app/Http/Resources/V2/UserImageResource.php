<?php
namespace App\Http\Resources\V2;

class UserImageResource extends Resource
{
    public function toArray($request)
    {
        $data = [
            'id'      => $this->id,
            'user_id' => $this->user_id,
            'image'   => $this->image,
            'is_main' => $this->is_main,
            'image_type_id' => $this->image_type_id
        ];

        return $data;
    }
}