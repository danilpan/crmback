<?php
namespace App\Http\Resources\V2;

class ProductCategoryResource extends Resource
{
    public function toArray($request)
    {
        $data   = [
            'id'            => $this->id,
            'name'          => $this->name,
            'is_work'       => $this->is_work,
            'organization'  => OrganizationResource::make($this->whenLoaded('organization')),
            'key'           => $this->key,
        ];

        return $data;
    }
}