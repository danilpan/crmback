<?php
namespace App\Http\Requests\Api\V2;


class DeliveryTypeProjectsRequest extends Request
{
    public function rules()
    {
        return [
            'id' => 'required|integer',
            'project' => 'required|exists:projects,id',
            'geo' => 'required|exists:geo,id',
        ];
    }
}
