<?php
namespace App\Http\Requests\Api\V2;

class UnloadRequest extends Request
{
    public function rules()
    {
        return [
            'name' => 'required|max:180',
            'comment' => 'nullable|max:200',
            'organization_id' => 'required|integer',
            'config' => 'required',
            'is_work' => 'required',
        ];
    }
}