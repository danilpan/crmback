<?php

namespace App\Http\Requests\Api\V2;

class AtsQueueAttachOperatorsRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "caller_ids" => 'sometimes|array',
            "caller_ids.*" => 'integer|exists:sip_caller_ids,id',
            "sorting" => 'sometimes|array',
            "sorting.*" => 'sometimes|integer',
        ];
    }
    
    public function messages()
    {
        return [
            "caller_ids.*.exists" => 'Значение должно являться ID существующего Caller ID',
            "caller_ids.required" => 'Caller ID обязательны для заполнения'
        ];
    }
}
