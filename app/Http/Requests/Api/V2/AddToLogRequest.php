<?php

namespace App\Http\Requests\Api\V2;


class AddToLogRequest extends Request
{
    public function rules()
    {
        $rules  = [
            'action'            => 'required',
            'info'              => 'required'
        ];

        return $rules;
    }
}
