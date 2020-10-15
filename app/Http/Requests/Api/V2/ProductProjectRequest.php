<?php
namespace App\Http\Requests\Api\V2;


class ProductProjectRequest extends Request
{
    public function rules()
    {
        return [
            'id' => 'required|integer',
            'products' => 'required|array'
        ];
    }
}
