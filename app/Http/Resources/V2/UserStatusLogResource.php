<?php

namespace App\Http\Resources\V2;


class UserStatusLogResource extends Resource
{
    public function toArray($request)
    {
        $data   = [
            'id'          => $this->id,
            'ats_user_id' => $this->ats_user_id,
            'status_id'   => $this->status_id,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
        ];

        return $data;
    }
}
