<?php
namespace App\Http\Resources\V2;

class OrderProductResource extends Resource
{
    public function toArray($request)
    { 
     
        $data   = [                        
            'product_id'          => $this->id,
            'comment'             => '',
            'name'                => $this->name,
            'price'               => $this->price_online,
            'upsale'              => 0,
            'quantity'            => 1,
            'show'                => false,
            'cart'                => true,
        ];       

        return $data;
    }
}
