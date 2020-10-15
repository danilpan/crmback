<?php

namespace App\Http\Requests\Api\V2;

class UserStatusLogRefreshRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'arr' => "required|string",
            'act' => "required|string|size:23",//update_status_operators
            'key' => "required|string|size:32",
        ];
    }
}
