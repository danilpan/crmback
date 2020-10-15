<?php
namespace App\Http\Requests\Api\V2;

class AttemptRequest extends Request
{

    public function rules()
    {
        return [
            'source' => 'required',
            'organization_id' => 'required:integer',
            'body' => 'required',
            'image' => 'nullable'
        ];
    }
}