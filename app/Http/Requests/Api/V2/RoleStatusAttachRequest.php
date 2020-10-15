<?php
namespace App\Http\Requests\Api\V2;


class RoleStatusAttachRequest extends Request
{
    public function rules()
    {
        return [
            'role_id' => 'required|integer',
            'status_id' => 'required|integer',
            'status_param_change' => 'required|max:10',
            'status_param_value' => 'required|boolean'
        ];
    }
}
