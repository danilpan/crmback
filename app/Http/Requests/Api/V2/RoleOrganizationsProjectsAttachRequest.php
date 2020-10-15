<?php
namespace App\Http\Requests\Api\V2;


class RoleOrganizationsProjectsAttachRequest extends Request
{
    public function rules()
    {
        return [
            'role_id' => 'required|integer',
            'organizations' => 'array',
            'projects' => 'array',
            'is_deduct_organization' => 'boolean',
            'is_deduct_project' => 'boolean'
        ];
    }
}
