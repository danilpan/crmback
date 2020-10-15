<?php
namespace App\Http\Requests\Api\V2;


class EntityParamRequest extends Request
{
    public function rules()
    {
        return [
            'parent_id' => 'nullable|integer',
            'entity_id' => 'required|integer',
			'name' => 'required|max:255',
			'description' => '|max:255',
			'parameter' => 'required|max:255'
        ];
    }
}
