<?php

namespace App\Http\Resources\V2;


class LnkRoleGeoResource extends Resource
{

    public function toArray($request)
    {
        $data   = [
            'id'    => $this->id,
            'role_id' => $this->role_id,
            'geos' => $this->geos,
            'is_deduct_geo' => $this->is_deduct_geo
        ];

        return $data;
    }

}