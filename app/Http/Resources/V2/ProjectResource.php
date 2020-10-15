<?php

namespace App\Http\Resources\V2;
use App\Models\Geo;

class ProjectResource extends Resource
{
    public function toArray($request)
    {
        $data   = [
            'id'                    => $this->id,
            'title'                 => $this->title,
            'description'           => $this->description,
            'organization_id'       => ["value"=>$this->organization['id'],"text"=>$this->organization['title']],
            'organizations'         => $this->organization->only(['id', 'title']),
            'category_id'           => ($this->category!=null) ? ["value"=>$this->category['id'],"text"=>$this->category['name']] : null,
            'project_category'      => $this->category,
            'name_for_client'       => $this->name_for_client,
            'sms_sender'            => $this->sms_sender,
            'hold'                  => $this->hold,
            'url'                   => $this->url,
            'is_private'            => $this->is_private,
            'is_call_tracking'      => $this->is_call_tracking,
            'is_authors'            => $this->is_authors,
            'is_resale'             => $this->is_resale,
            'is_postcode_info'      => $this->is_postcode_info,
            'image'                 => $this->image,
            'gender'                => $this->gender,
            'postclick'             => $this->postclick,
            'geos'                  => $this->geo()->get(),
            'traffics'              => $this->traffics()->get(),	    
	        'project_page'          => ProjectPageResource::collection($this->whenLoaded('project_page')),
	        'age'		            => $this->age,
            'delivery_types'        => ($this->delivery_types)?$this->delivery_types:[],            
            'key'                   => $this->key,
            'created_at'            => ($this->created_at) ? $this->created_at->format('Y-m-d H:i:s') : null,
            'summary'               => $this->summary,
            'items'                 => $this->items,
            'project_category_kc'   => $this->project_category_kc,
            'project_category_kc_id'   => $this->project_category_kc_id,
            'replica'               => $this->replica,
            'operator_notes'        => $this->operator_notes
        ];
        
        if ($this->delivery_types) {
            foreach ($this->delivery_types as $key => $dt) {
                $geo = Geo::find($dt->pivot->geo_id);
                if ($geo) {
                    $this->delivery_types[$key]->geo = $geo->name_ru;
                } else {
                    $this->delivery_types[$key]->geo = '';
                }
            }
        }

        return $data;
    }
}
