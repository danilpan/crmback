<?php
namespace App\Http\Resources\V2;

class SmsProviderResource extends Resource
{
    public function toArray($request)
    {
        
        $data   = [
            'id'                    => $this->id,
            'name'                  => $this->name,
            'sms_provider'          => $this->sms_provider,
            'created_at'            => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at'            => $this->updated_at->format('Y-m-d H:i:s'),
            'data'                  => json_decode($this->data)
        ];

        return $data;
    }
}