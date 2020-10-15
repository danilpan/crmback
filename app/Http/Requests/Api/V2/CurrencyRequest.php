<?php
namespace App\Http\Requests\Api\V2;


class CurrencyRequest extends Request
{
    public function rules()
    {
        return [
            'name' => 'required|max:255'
        ];
    }
}
