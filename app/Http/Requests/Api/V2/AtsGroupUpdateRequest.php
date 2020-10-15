<?php

namespace App\Http\Requests\Api\V2;

class AtsGroupUpdateRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'max:80|unique:ats_groups,name,'.$this->id,
            'description' => 'max:255',
            'is_work' => 'boolean',
            'ats_id' => 'integer|min:1'
        ];
    }
}
