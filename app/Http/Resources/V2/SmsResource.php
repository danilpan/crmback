<?php
namespace App\Http\Resources\V2;

class SmsResource extends Resource
{
    public function toArray($request)
    {
        
        $data   = [
            'id'                    => $this->id,
            'message'               => $this->message,
            'phone'                 => $this->phone,
            'status'                => $this->status,
            'user'                  => $this->user->first_name.' '.$this->user->last_name,
            'type'                  => $this->type,
            'created_at'            => $this->created_at->format('Y-m-d H:i:s'),
        ];

        return $data;
    }
}