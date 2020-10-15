<?php

namespace App\Http\Resources\V2;


class OutRouteResource extends Resource
{
    public function toArray($request)
    {
        $data   = [
            'id'            => $this->id,
            'name'          => $this->name,
            'comment'       => $this->comment,
            'mask'          => $this->mask,
            'replace_count' => $this->replace_count,
            'prefix'        => $this->prefix,
            'trunks1'       => $this->trunks1,
            'trunks2'       => $this->trunks2,
            'trunks_p2'     => $this->trunks_p2,
            'trunks_p1'     => $this->trunks_p1,
            'ats_group_id'  => $this->ats_group_id,
            'provider_id'   => $this->provider_id,
            'provider_name' => "Не назначен",
            'is_work'       => $this->is_work,
        ];
        
        if ($this->hst) {
            $data['history'] = $this->hst;
        }
        
        if ($this->provider_id) {
            $data['provider_name'] = $this->provider->name;
        }

        return $data;
    }
}
