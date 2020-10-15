<?php

namespace App\Http\Requests\Api\V2;

class AtsGroupRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|unique:ats_groups,name|max:80',
            'description' => 'max:255',
            'is_work' => 'boolean',
            'ats_id' => 'required|integer|exists:ats,id',
            'organization_id' => 'exists:organizations,id'
        ];
    }
    
    public function messages()
    {
        return [
            'organization_id.exists' => 'Значение должно являться ID существующей организации',
            'ats_id.exists' => 'Значение должно являться ID существующего сервера АТС',
        ];
    }
}
