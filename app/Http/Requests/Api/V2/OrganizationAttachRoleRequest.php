<?php

namespace App\Http\Requests\Api\V2;


class OrganizationAttachRoleRequest extends Request
{
    public function rules()
    {
        $rules  = [
            'role_id'       => 'integer|min:1'
        ];

        return $rules;
    }
}
