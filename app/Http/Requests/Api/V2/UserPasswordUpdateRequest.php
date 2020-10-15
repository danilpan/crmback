<?php


namespace App\Http\Requests\Api\V2;


class UserPasswordUpdateRequest extends Request
{
    public function rules()
    {
        return [
            'current_password' => 'required',
            'new_password'     => 'required|confirmed|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*#?&_]/|different:current_password'
        ];
    }
}