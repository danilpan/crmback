<?php
namespace App\Http\Requests\Api\V2;


class UserSetShowIsWorkRequest extends Request
{
    public function rules()
    {
        $id     = $this->route()->parameter('id');
        return [
            'is_show_work'     => 'required'
        ];
    }
}
