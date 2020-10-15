<?php
/**
 * Created by PhpStorm.
 * User: timur
 * Date: 18.02.19
 * Time: 22:34
 */

namespace App\Http\Requests\Api\V2;


class DeviceTypeRequest extends Request
{
    public function rules()
    {
        return [
            'name' => 'required|max:255',
            'is_show' => 'required|boolean'
        ];
    }
}