<?php
namespace App\Http\Requests\Api\V2;

class SmsCreateRequest extends Request
{
    public function rules()
    {
        return [
            'key' => 'required',
            'sms_template_id' => 'required|integer',
            'sms_phone' => 'required|integer'
        ];
    }
}