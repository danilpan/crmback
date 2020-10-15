<?php

namespace App\Http\Requests\Api\V2;


class OrganizationUpdateRequest extends Request
{
    public function rules()
    {
        $rules  = [
            'title'         => 'required|min:3',
            //'permission_id' => 'int|min:1',
            'role_id'       => 'integer|min:1',
            'parent_id'     => 'nullable|integer',
            'is_company'    => 'nullable',
            'api_key'    => 'string|size:32|unique:organizations,api_key',

        ];

        return $rules;
    }
}
