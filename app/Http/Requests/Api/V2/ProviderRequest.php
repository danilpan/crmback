<?php

namespace App\Http\Requests\Api\V2;

class ProviderRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:80',
            'img' => 'max:255',
            'comment' => 'max:255',
            'logo' => '',
        ];
    }
}
