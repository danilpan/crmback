<?php
/**
 * Created by PhpStorm.
 * User: timur
 * Date: 18.02.19
 * Time: 17:43
 */

namespace App\Http\Resources\V2;


class DeviceTypeResource extends Resource
{
    public function toArray($request)
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'is_show' => $this->is_show
        ];

        return $data;
    }
}