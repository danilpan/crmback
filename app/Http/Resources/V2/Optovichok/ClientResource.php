<?php
namespace App\Http\Resources\V2\Optovichok;

use App\Http\Resources\V2\Resource;

class ClientResource extends Resource
{
    public function toArray($request)
    {
        $data   = [
            'id'                => $this->id,
            "client_name"       => $this->client_name,
            'phone'             => $this->phone,
            'iin'               => $this->iin,
            'type'              => ['id' => $this->type, 'title' => $this->client_type->title],
            'advert_source'     => ['id'=> $this->advert_source->id, 'title' => $this->advert_source->name],
            'organizations'      => ['id'=> $this->organization->id, 'title' => $this->organization->title, 'parent_id' => $this->organization->parent_id, 'is_company' => $this->organization->is_company],
        ];

        return $data;
    }
}