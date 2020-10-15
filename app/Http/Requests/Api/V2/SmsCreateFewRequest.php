<?php
namespace App\Http\Requests\Api\V2;


class SmsCreateFewRequest extends Request
{
    public function rules()
    {
        return [
            'keys' => 'required',
            'sms' => 'required|max:320',
        ];
    }
}