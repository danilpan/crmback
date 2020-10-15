<?php

namespace App\Http\Requests\Api\V2;


class DxSearchRequest extends Request
{
    public function rules()
    {
        $rules  = [
            'skip'              => 'integer',
            'take'              => 'integer|min:1|max:200',
            'requireTotalCount' => 'string'
        ];

        return $rules;
    }
}
