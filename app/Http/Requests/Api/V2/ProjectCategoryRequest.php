<?php
namespace App\Http\Requests\Api\V2;


class ProjectCategoryRequest extends Request
{
    public function rules()
    {
        return [
            'organization_id' => 'required|integer',
			'name' => 'required|max:255',
			'is_work' => 'required|boolean'
        ];
    }
}
