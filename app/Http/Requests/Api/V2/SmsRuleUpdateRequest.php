<?php
namespace App\Http\Requests\Api\V2;


class SmsRuleUpdateRequest extends Request
{
    public function rules()
    {
        return [
            'is_work' => 'required'
        ];
    }
}