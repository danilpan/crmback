<?php
namespace App\Http\Requests\Api\V2;


class BlackListRequest extends Request
{
    public function rules()
    {
        return [
			'phone' => 'required|max:15',			
			'key' => 'required|exists:orders',	
        ];
    }
}
