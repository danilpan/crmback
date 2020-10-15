<?php
namespace App\Http\Requests\Api\V2;


class ProjectPagePhoneRequest extends Request
{
    public function rules()
    {
        return [
            'project_page_id' => 'required|integer',
            'phone' => 'required|max:255'
        ];
    }
}
