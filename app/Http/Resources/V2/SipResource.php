<?php

namespace App\Http\Resources\V2;


class SipResource extends Resource
{
    public function toArray($request)
    {
        $data   = [
            'id'            => $this->id,
            'host'          => $this->host,
            'description'   => $this->description,
            'port'          => $this->port,
            'passwd'        => $this->passwd,
            'login'         => $this->login,
            'max_channels'  => $this->max_channels,
            'template'      => $this->template,
            'connect_type'  => $this->connect_type,
            'ats_group_id'  => $this->ats_group_id,
            'is_work'       => $this->is_work,
            'ats_group'     => $this->atsGroup,
            'organizations' => $this->atsGroup->organizations
        ];

        return $data;
    }
}
