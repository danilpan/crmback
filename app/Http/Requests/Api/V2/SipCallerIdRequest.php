<?php

namespace App\Http\Requests\Api\V2;

class SipCallerIdRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'sip_id' => 'sometimes|exists:sips,id',
            'ats_user_id' => 'sometimes|exists:ats_users,id',
            'caller_id' => 'required|string|max:20|unique:sip_caller_ids,caller_id',
            'ats_queue_id' => 'sometimes|integer|exists:ats_queues,id',
        ];
    }
    
    public function messages()
    {
        return [
            'sip_id.exists' => 'Поле sip_id должно содержать ID существующего транка',
            'ats_user_id.exists' => 'Поле ats_user_id должно содержать ID существующего пользователя АТС',
        ];
    }
}
