<?php

namespace App\Http\Requests\Api\V2;

class AtsUpdateRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
          'ip' => 'required|ip|unique:ats,ip,'.$this->id,
          'key' => 'max:32',
          'name' => 'required|max:255|unique:ats,name,'.$this->id,
          'description' => 'max:255',
          'is_work' => 'boolean',
          'is_default' => 'boolean'
        ];
    }
}
