<?php

namespace App\Http\Requests\Api\V2;

class AtsUserRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'port' => 'digits_between:1,6',
            'passwd' => 'required|string|max:80',
            'login' => 'required|string|unique:ats_users,login|max:80',
            'max_channels' => 'required|integer|max:255',
            'type' => 'required|string|max:16',
            'ats_group_id' => 'required|integer|exists:ats_groups,id',
            'user_id' => 'sometimes|integer|exists:users,id',
            'is_work' => 'boolean',
            'comment' => 'sometimes|string|max:255',
            'out_calls' => 'boolean',
            'option_in_call' => 'boolean',
            'cid' => 'sometimes|string|size:4',
        ];
    }
    
    public function messages()
    {
        return [
            'ats_group_id.exists' => 'Поле ats_group_id должно содержать ID существующей группы АТС',
            'user_id.exists' => 'Поле user_id должно содержать ID существующего пользователя системы'
        ];
    }
}
