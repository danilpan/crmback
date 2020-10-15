<?php
namespace App\Http\Requests\Api\V2;

class ProductCategoryRequest extends Request
{
    public function rules()
    {
        return [
            'name'              => 'required|max:255',
            'is_work'           => 'nullable|integer',
        ];
    }
}
