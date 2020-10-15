<?php
namespace App\Http\Requests\Api\V2;


class RoleRequest extends Request
{
    public function rules()
    {
        return [
			'name' => 'required|max:255',
			'description' => 'required|max:255',
            'group_id' => 'required|integer',
            'creator_organization_id' => 'integer',
            'is_need_display_notify' => 'boolean'
        ];
    }
}
