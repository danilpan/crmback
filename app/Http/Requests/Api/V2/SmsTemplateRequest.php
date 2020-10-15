<?php


namespace App\Http\Requests\Api\V2;


class SmsTemplateRequest extends Request
{
    public function rules()
    {
        return [
            'name'            => 'required|max:255',
            'sms_text'        => 'required',
            'is_work'         => 'required|boolean',
            'organizations'   => 'required|array'
        ];
    }
}