<?php
namespace App\Http\Resources\V2;

class PermissionResource extends Resource
{
    public function toArray($request)
    {
        $data   = [
            'id'            => $this->id,
            'title'         => $this->title,
            'api'           => (array)$this->api,
            'order'         => (array)$this->order,
            'orders_data'   => (array)$this->orders_data,
            'orders_fields' => (array)$this->orders_fields
        ];

        return $data;
    }
}