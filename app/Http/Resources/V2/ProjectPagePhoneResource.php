<?php

namespace App\Http\Resources\V2;


class ProjectPagePhoneResource extends Resource
{
    public function toArray($request)
    {
        $data   = [
            'id'    => $this->id,
            'project_page_id'    => $this->project_page_id,
            'phone' => $this->phone,
        ];

        return $data;
    }
}
