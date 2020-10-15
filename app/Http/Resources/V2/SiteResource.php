<?php
namespace App\Http\Resources\V2;


class SiteResource extends Resource
{
    public function toArray($request)
    {
        $data   = [
            'id'    => $this->id,
            'url'   => $this->url,
            'title'   => $this->title
        ];

        return $data;
    }
}