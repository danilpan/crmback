<?php
namespace App\Http\Requests\Api\V2;


class RoleGroupParamRequest extends Request
{
    public function rules()
    {
        return [
            'role_group_id' => 'required|max:255',
        ];
    }
}