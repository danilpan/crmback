<?php
namespace App\Http\Requests\Api\V2;


class PermissionCreateRequest extends Request
{
    public function rules()
    {
        return [
            'title'         => 'required|min:3',
            'api'           => 'array',
            'orders_data'   => 'array',
            'orders_fields' => 'array'
        ];
    }
}