<?php

namespace App\Http\Requests\Api\V2;

class AtsGroupAttachOrganizationsRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "organizations" => 'required|array',
            "organizations.*" => 'integer|exists:organizations,id'
        ];
    }
    
    public function messages()
    {
        return [
            "organizations.*.exists" => 'Значение должно являться ID существующей организации',
            "organizations.required" => 'Организации обязательны для заполнения'
        ];
    }
}
