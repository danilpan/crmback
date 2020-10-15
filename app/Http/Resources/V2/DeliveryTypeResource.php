<?php
namespace App\Http\Resources\V2;

class DeliveryTypeResource extends Resource
{
    public function toArray($request)
    {
        $data   = [
            'id'    => $this->id,
            'name'  => $this->name,
            'price'  => $this->price,
            'surplus_percent' => $this->surplus_percent,
            'organizations' => $this->organization,
            'organization_id' => ["value"=>$this->organization['id'],"text"=>$this->organization['title']],
            'is_work'  => $this->is_work,
            'is_show'  => $this->is_show,
            'peoples'  => $this->peoples,
            'priority'  => $this->priority,
            'postcode_info'  => $this->postcode_info,
            'key'  => $this->key,
        ];

        return $data;
    }
}
