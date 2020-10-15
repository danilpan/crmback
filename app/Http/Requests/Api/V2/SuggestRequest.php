<?php
namespace App\Http\Requests\Api\V2;

class SuggestRequest extends Request
{
    public function rules()
    {
        $rules  = [
            'q'         => 'max:400',
            'limit'     => 'integer|min:1|max:100',
            'filters'   => 'array',
        ];

        return $rules;
    }
}
