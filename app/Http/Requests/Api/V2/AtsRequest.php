<?php

namespace App\Http\Requests\Api\V2;

class AtsRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
          'ip' => 'required|unique:ats,ip|ip',
          'key' => 'max:32',
          'name' => 'required|unique:ats,name|max:255',
          'description' => 'max:255',
          'is_work' => 'boolean',
          'is_default' => 'boolean'
        ];
    }
}
