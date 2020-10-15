<?php
namespace App\Http\Requests\Api\V2;


class EntityParamByRolePermittedRequest extends Request
{
    public function rules()
    {
        return [
            'parent_id' => 'nullable|integer',
            'entity_id' => 'required|integer',
            'role_id' => 'required|integer',
            'other_role_id' => 'required|integer',
        ];
    }
}
