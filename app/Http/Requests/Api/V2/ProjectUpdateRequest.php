<?php
namespace App\Http\Requests\Api\V2;


class ProjectUpdateRequest extends Request
{
    public function rules()
    {
        return [
            'title' => 'required|max:180',
            'description' => 'max:1000',
            'organization_id' => 'nullable|integer',
            'project_category_kc_id' => 'required|integer',
            'name_for_client' => 'required|max:180',
			'sms_sender' => 'required|max:180',
			'hold' => 'nullable|integer',
			'url' => 'nullable|url',
			'is_private' => 'nullable',
			'is_call_tracking' => 'nullable',
			'is_authors' => 'nullable',
			'is_resale' => 'nullable',
			'is_postcode_info' => 'nullable',
			'category_id' => 'nullable|integer',
			'image' => 'nullable|string',
			'gender' => 'nullable|integer',
			'postclick' => 'nullable|integer',
			'age' => 'nullable|array',
            'geos'    => 'array',
			'traffics' => 'array',
            'replica' => 'nullable',
            'operator_notes' => 'nullable'
        ];
    }
}
