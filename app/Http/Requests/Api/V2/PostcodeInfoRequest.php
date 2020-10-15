<?php
namespace App\Http\Requests\Api\V2;


class PostcodeInfoRequest extends Request
{
    public function rules()
    {
        return [
            //'file' => 'required|file'
            'file' => ''
        ];
    }
}
