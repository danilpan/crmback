<?php
namespace App\Http\Requests\Api\V2;


class GeoRequest extends Request
{
    public function rules()
    {
        return [
            'name_ru' => 'required|max:255',
            'code' => 'required|max:240'
        ];
    }
}