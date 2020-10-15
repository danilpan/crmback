<?php
namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class Resource extends JsonResource
{

    public static $wrap = null;

    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
