<?php
namespace App\Http\Requests\Api\V2;

use Illuminate\Validation\Rule;


class UserUpdateRequest extends Request
{
    public function rules()
    {
//        dd($this->route()->parameter('id'));

        $id     = $this->route()->parameter('id');
        $rules  = [
            'first_name'    => 'required|min:3|max:100',
            'last_name'     => 'required|min:3|max:100',
            'middle_name'   => 'nullable',
            'phone'         => 'sometimes|nullable|regex:/^(7)[0-9]{10}$/',
            'login'    => [
                'required',
                Rule::unique('users')->ignore($id)
            ],
            'mail'     => [
                'sometimes',
                'nullable',
                'email',
                Rule::unique('users')->ignore($id)
            ],
            'password'      => 'nullable|confirmed|min:6',
            'is_work'       => 'required',
            'out_calls'     => 'required',
            'phone_office'  => 'nullable|max:4',
            'organization_id' => 'required',
            'mainlink'  => 'nullable',
            'telegram'  => 'nullable',
            'plates'    => 'nullable'
        ];


        return $rules;
    }
}
