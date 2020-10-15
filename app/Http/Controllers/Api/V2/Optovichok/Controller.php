<?php
namespace App\Http\Controllers\Api\V2\Optovichok;

class Controller extends \App\Http\Controllers\Api\V2\Controller
{

    protected function getResourceName($model)
    {
        $parts      = explode('\\', get_class($model));
        $resource   = 'App\Http\Resources\V2\Optovichok\\' .  end($parts) . 'Resource';

        return $resource;
    }

}