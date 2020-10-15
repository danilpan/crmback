<?php
namespace App\Http\Requests\Api\V2;


class DeliveryTypesRequest extends Request
{
    public function rules()
    {
        return [
            'organization_id' => 'required|integer',
			'name' => 'required|max:255',
			'price' => 'required|numeric',
            'surplus_percent' => 'sometimes|numeric',
			'is_work' => 'required|boolean',
			'is_show' => 'required|boolean'
        ];
    }
}
