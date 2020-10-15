<?php

namespace App\Http\Resources\V2;


class ProjectGasketResource extends Resource
{
    public function toArray($request)
    {
        $data   = [
            'id'    => $this->id,
            'project_id'    => $this->project_id,
            'name' => $this->name,
            'link' => $this->link
        ];

        return $data;
    }
}
