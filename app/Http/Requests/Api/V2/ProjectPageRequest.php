<?php
namespace App\Http\Requests\Api\V2;


class ProjectPageRequest extends Request
{
    public function rules()
    {
        return [
            'project_id' => 'required|integer',
            'name' => 'required|max:255',
            'link' => 'required|max:255'
        ];
    }
}