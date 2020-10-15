<?php
namespace App\Http\Resources\V2;

class OrderSenderResource extends Resource
{
    public function toArray($request)
    {
        $data = [
            'id'                =>  $this->id,
            'organization_id'   => ["value"=>$this->organization['id'],"text"=>$this->organization['title']],
            'name'              =>  $this->name,
            'phone'             =>  $this->phone,
            'iin'               =>  $this->iin,
            'is_work'           =>  $this->is_work,
            'organizations'     =>  $this->organization,
        ];

        return $data;
    }
}