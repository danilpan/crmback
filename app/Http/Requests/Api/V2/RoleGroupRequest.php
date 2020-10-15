<?php
namespace App\Http\Requests\Api\V2;


class RoleGroupRequest extends Request
{
    public function rules()
    {
        return [
			'name' => 'required|max:255',
			'description' => 'required|max:255',
            'creator_organization_id' => 'required|integer'
        ];
    }
}
