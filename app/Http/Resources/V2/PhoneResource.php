<?php
namespace App\Http\Resources\V2;


class PhoneResource extends Resource
{
    public function toArray($request)
    {
        $data   = [
            'id'        => $this->id,
            'phone'     => $this->phone
        ];

        return $data;
    }
}