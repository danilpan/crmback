<?php
namespace App\Http\Requests\Api\V2;

class UnloadAPIRequest extends Request
{
    public function rules()
    {
        return [
            'act' => 'max:180',
            'key' => 'required|max:200',
            'start' => 'integer',
            'stop' => 'integer',
            'format' => 'max:5',
            'leads_prm' => 'max:200'
        ];
    }
}