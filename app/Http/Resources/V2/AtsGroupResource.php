<?php

namespace App\Http\Resources\V2;


class AtsGroupResource extends Resource
{
    public function toArray($request)
    {
        $data   = [
            'id'    => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'is_work'  => $this->is_work,
            'ats_id'  => $this->ats_id
        ];
        
        if ($this->organizations) {
            foreach ($this->organizations as $org) {
                $data['organizations'][] =$org['id'];
            }
        }
        
        if ($this->ats) {
            $data['ats_name'] = $this->ats->name;
        } else {
            $data['ats_name'] = '';
        }

        return $data;
    }
}
