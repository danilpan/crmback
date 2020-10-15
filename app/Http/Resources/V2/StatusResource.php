<?php
namespace App\Http\Resources\V2;

use Auth;

class StatusResource extends Resource
{

    public function toArray($request)
    {
        
            $data   = [
                'id'    => $this->id,
                'status_title' => ($this->status_title)?$this->status_title : null,                        
                'parent_id'  => $this->parent_id,            
                'disabled'  => $this->disabled,       
                'visible'  => $this->visible==null ? true : $this->visible,       
                'check_permission'  => $this->check_permission,            
                'organization_id' => $this->organization_id,
                'name' => $this->name,
                'desc' => $this->desc,            
                'is_work' => $this->is_work,
                'type' => $this->type,
                'color' => $this->color,
                'sort' => $this->sort,           
                'children' => $this->children,
                'organization_name' => $this->organization_name,
                'key' => $this->key                                        
            ];            

            if($this->can_history)
                $data['history'] = ($this->history) ? OrderHistoryResource::collection($this->history) : [];  

        
        return $data;
        
    }
}
