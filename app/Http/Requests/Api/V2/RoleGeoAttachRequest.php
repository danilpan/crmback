<?php

namespace App\Http\Requests\Api\V2;


class RoleGeoAttachRequest extends Request
{
    public function rules()
    {
        return [
            'role_id' => 'required|integer',
            'geos' => 'array',
            'is_deduct_geo' => 'boolean'
        ];
    }
}