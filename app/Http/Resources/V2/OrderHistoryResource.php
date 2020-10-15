<?php
namespace App\Http\Resources\V2;

class OrderHistoryResource extends Resource
{
    
    public function toArray($request)
    { 
        $data   = [                        
            'user_name'           => $this->users->first_name.' '.$this->users->last_name,
            'body'                => is_string($this->body)?json_decode($this->body):$this->body,
            'created_at'          => $this->created_at->format('Y-m-d H:i:s')            
        ];       

        return $data;
    }
}