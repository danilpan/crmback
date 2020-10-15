<?php
namespace App\Http\Resources\V2;

class RelatedProductResource extends Resource
{
    public function toArray($request)
    { 
     
        $data   = [                        
            'id'             => $this->id,            
            'name'           => $this->name,
            'price_online'   => $this->price_online   
        ];       

        return $data;
    }
}
