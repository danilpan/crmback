<?php
namespace App\Http\Requests\Api\V2;


class StatusAndRoleRequest extends Request
{
    public function rules()
    {
        return [
            'role_id' => 'nullable|integer',
            'other_role_id' => 'nullable|integer',
            'status_id' => 'required|integer',
        ];
    }
}
