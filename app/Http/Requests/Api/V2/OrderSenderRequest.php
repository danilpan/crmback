<?php
namespace App\Http\Requests\Api\V2;

class OrderSenderRequest extends Request
{
    public function rules()
    {
        return [
            'organization_id' => 'required|integer',
            'name'    => 'required|max:64',
            'iin'     => 'required|min:9|max:12',
            'phone'   => 'required',
            'is_work' => 'nullable|boolean'
        ];
    }
}
