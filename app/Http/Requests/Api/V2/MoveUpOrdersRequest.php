<?php

namespace App\Http\Requests\Api\V2;

class MoveUpOrdersRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'orders' => 'required|array:integer',
            'orders.*' => 'array',
            'orders.*.*' => 'integer|exists:orders,id',
        ];
    }
}
