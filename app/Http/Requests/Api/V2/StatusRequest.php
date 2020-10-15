<?php
namespace App\Http\Requests\Api\V2;


class StatusRequest extends Request
{
    public function rules()
    {
		return [
            'parent_id' => 'nullable|integer',            
            'name' => 'required|max:100',
            'title' => 'nullable|max:100',
            'status_title' => 'max:100',            
            'desc' => 'max:1000',            
            'is_work' => 'required|integer',			
			'color' => 'max:1000',
			'sort' => 'integer'
        ];
    }
}