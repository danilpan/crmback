<?php
namespace App\Http\Requests\Api\V2;


class EntityAndRoleRequest extends Request
{
    public function rules()
    {
        return [
            'role_id' => 'nullable|integer',
            'entity_id' => 'required|integer',
        ];
    }
}
