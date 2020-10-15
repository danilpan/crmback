<?php

namespace App\Http\Resources\V2;


class SmsRuleResource extends Resource
{
    public function toArray($request)
    {
        $data   = [
            'id'    => $this->id,
            'geo_id' => $this->geo_id,            
            'type' => $this->type,
            'is_work' => $this->is_work,
            'created_at' => $this->created_at->format('Y-m-d H:i:s')
        ];

        return $data;
    }
}
