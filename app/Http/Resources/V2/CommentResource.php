<?php
namespace App\Http\Resources\V2;

class CommentResource extends Resource
{
    public function toArray($request)
    {

        $data   = [            
            'id'                       => $this->id,
            'text'                     => $this->text,
            'user_id_name'             => $this->user->first_name.' '.$this->user->last_name,
            'created_at'               => $this->created_at->format('Y-m-d H:i:s') 
        ];       

        return $data;
    }
}