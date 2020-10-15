<?php

namespace App\Http\Requests\Api\V2;

class SipRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'host' => 'required|string|max:255',
            'description' => 'max:255',
            'port' => 'required|digits_between:1,6',
            'passwd' => 'sometimes|nullable|string|max:80',
            'login' => 'sometimes|nullable|string|max:80',
            'max_channels' => 'required|integer|max:255',
            'template' => 'required|string|max:255',
            'connect_type' => 'required|string|max:5',
            'ats_group_id' => 'required|integer|exists:ats_groups,id',
            'is_work' => 'boolean'
        ];
    }
    
    public function messages()
    {
        return [
            'ats_group_id.exists' => 'Поле ats_group_id должно содержать ID существующей группы АТС'
        ];
    }
}
