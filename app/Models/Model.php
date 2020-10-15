<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;

class Model extends BaseModel implements ModelIterface
{
    public function fromJson($value, $asObject = false)
    {
        if(is_array($value)) {
            return $value;
        }

        return json_decode($value, ! $asObject);
    }

}
