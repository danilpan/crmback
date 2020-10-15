<?php
namespace App\Http\Requests\Api\V2;


class UserAuthRequest extends Request
{
    public function rules()
    {
        return [
            'login'     => 'required|min:2|max:100',
            'password'  => 'required|max:100',
            //'recaptcha'  => 'required'
        ];
    }
}
