<?php
namespace App\Http\Resources\V2;

class SaleResource extends Resource
{
    public function toArray($request)
    {

        $data   = [            
            'id'                  => $this->id,
            'product_id'          => $this->product_id,
            'comment'             => $this->comment,
            'name'                => $this->name,
            'price'               => $this->price,
            'upsale'              => ($this->upsale)?$this->upsale:0,
            'quantity'            => $this->quantity,
            'show'                => false,
        ];       

        return $data;
    }
}
