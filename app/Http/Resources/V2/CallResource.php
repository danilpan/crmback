<?php
namespace App\Http\Resources\V2;

class CallResource extends Resource
{

    public function toArray($request)
    {

        if(!isset($this->disposition_name) && !isset($this->disposition_class)){
            $disposition = \Calls::convertDisposition($this);    
        }else{
            $disposition = [
                'name' => $this->disposition_name,
                'class' => $this->disposition_class
            ];    
        }

        $manager_name = '';
        if(isset($this->manager->last_name)){
            $manager_name = $this->manager->last_name.' '.$this->manager->first_name;
        }elseif(is_string($this->manager)){
            $manager_name = $this->manager;
        }

        if(isset($this->queue->name)){
            $queue_name = $this->queue->name;
        }
        else{
            $queue_name = 'Unknown';
        }

        
        $data   = [
            'id'                    => $this->id,
            'queue_id'              => $this->queue_id,
            'queue_name'            => $queue_name,
            'step_id'               => $this->step_id,
            'weight'                => $this->weight,
            'rule_id'               => $this->rule_id,
            'key'                   => $this->key,
            'organizations'         => $this->organizations,
            'call_type'             => $this->call_type,
            'sip'                   => $this->sip,
            'manager'               => $manager_name,
            'order_id'              => $this->order_id,
            'order_key'             => $this->order_key,
            'phone'                 => $this->phone,
            'record_time'           => $this->record_time,
            'record_link'           => \Calls::getLink($this->time, $this->id, $this->ats_group_id),
            // Эмулируем текущее время
            // 'record_link'                  => \Calls::getLink(\Carbon\Carbon::now(), $this->id, 1), 
            'disposition'           => $this->disposition,
            //'test'                  => \Calls::convertDisposition($this),
            'disposition_name'      => $disposition['name'],
            'disposition_class'     => $disposition['class'],
            'time'                  => ($this->time)?$this->time->format('Y-m-d H:i:s'):'',
            'billing_time'          => $this->billing_time,
            'duration_time'         => $this->duration_time,
            'link'                  => $this->link,
            'call_statuses'         => CallStatusResource::collection($this->call_statuses),
            'call_statuses_string'  => $this->call_statuses_string,
            'show'                  => false,
            'ats_group_id'          => $this->ats_group_id,
        ];

        return $data;
    }
}