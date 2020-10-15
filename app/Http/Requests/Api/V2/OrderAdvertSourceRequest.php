<?php
/**
 * Created by PhpStorm.
 * User: timur
 * Date: 14.02.19
 * Time: 23:59
 */

namespace App\Http\Requests\Api\V2;


class OrderAdvertSourceRequest extends Request
{
    public function rules()
    {
        return [
          'name' => "required|max:255",
          'is_show' => "required|boolean"
        ];
    }
}