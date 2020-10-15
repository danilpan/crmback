<?php
namespace App\Http\Requests\Api\V2;


class CallDoRequest extends Request
{
    public function rules()
    {
        return [
			'phone_num' => 'required|max:1',
			'order_key' => 'required|max:255'
        ];
    }
}
