<?php
/**
 * Created by PhpStorm.
 * User: timur
 * Date: 15.02.19
 * Time: 16:47
 */

namespace App\Http\Resources\V2;


class OrderAdvertSourceResource extends Resource
{
    public function toArray($request)
    {
        $data   = [
            'id'    => $this->id,
            'name'  => $this->name,
            'is_show'  => $this->is_show,
        ];

        return $data;
    }
}