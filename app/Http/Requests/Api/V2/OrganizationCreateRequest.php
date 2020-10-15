<?php

namespace App\Http\Requests\Api\V2;


class OrganizationCreateRequest extends Request
{
    public function rules()
    {
        $rules  = [
            'title'         => 'required|min:3',
           // 'permission_id' => 'integer|min:1',
            'role_id'       => 'integer|min:1',
            'parent_id'       => 'nullable|integer',
            'is_company'    => 'nullable',
        ];

        return $rules;
    }
}
