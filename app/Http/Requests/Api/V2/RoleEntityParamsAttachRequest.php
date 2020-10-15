<?php
namespace App\Http\Requests\Api\V2;


class RoleEntityParamsAttachRequest extends Request
{
    public function rules()
    {
        return [
            'role_id' => 'required|integer',
            'entity_id' => 'required|integer',
            'entity_params' => 'array'
        ];
    }
}
