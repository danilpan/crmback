<?php
namespace App\Http\Requests\Api\V2;


class RoleCopySettingsRequest extends Request
{
    public function rules()
    {
        return [
            'role_from' => 'required|integer',
            'role_to' => 'required|integer',
        ];
    }
}
