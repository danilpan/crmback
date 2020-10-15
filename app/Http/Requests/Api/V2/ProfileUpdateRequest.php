<?php
namespace App\Http\Requests\Api\V2;

use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends Request
{
    public function rules()
    {
        $id     = $this->id;
        $rules  = [
            'first_name'    => 'required|min:3|max:100',
            'last_name'     => 'required|min:3|max:100',
            'middle_name'   => 'nullable',
            'phone'         => 'required|regex:/^(7)[0-9]{10}$/',
            'mail'     => [
                'nullable',
                'email',
                Rule::unique('users')->ignore($id)
            ],
            'telegram'                     => 'nullable',
            'plates'                       => 'nullable',
            'user_images'                  => 'array|max:6',
            'user_images.*.image_upload'   => 'nullable|file|mimes:jpeg,png,jpg,gif,svg',
            'user_images.*.id'             => 'nullable|integer',
            'user_images.*.is_main'        => 'nullable|boolean',
            'user_images.*.image_type_id'  => 'nullable|integer'
        ];

        return $rules;
    }
}