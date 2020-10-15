<?php
namespace App\Http\Resources\V2;

class CallStatusResource extends Resource
{
    public function toArray($request)
    {
        $manager_name = '';
        if(isset($this->manager->last_name)){
            $manager_name = $this->manager->last_name.' '.$this->manager->first_name;
        }elseif(is_string($this->manager)){
            $manager_name = $this->manager;
        }

        $data   = [
            'status'    => $this->status,
            'agent'     => $this->agent,
            'time'      => ($this->time)?$this->time->format('Y-m-d H:i:s'):'',            
            'manager'   => $manager_name
        ];

        return $data;
    }
}
