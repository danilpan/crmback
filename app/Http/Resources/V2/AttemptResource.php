<?php
namespace App\Http\Resources\V2;

class AttemptResource extends Resource
{
    public function toArray($request)
    {
        return [
            'id'    => $this->id,
            'source' => $this->source,
            'organization_id' => $this->organization_id,
            'body' => $this->body,
            'image' => $this->image,
            'created_at'  => ($this->created_at) ? $this->created_at->format('Y-m-d H:i:s') : null,
            'organizations' => $this->organization->only(['id', 'title']),
        ];
    }
}