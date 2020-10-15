<?php

namespace App\Http\Resources\V2;


class AtsQueueResource extends Resource
{
    public function toArray($request)
    {
        $data   = [
            'id'              => $this->id,
            'type'            => $this->type,
            'name'            => $this->name,
            'comment'         => $this->comment,
            'steps1'          => $this->steps1,
            'steps2'          => $this->steps2,
            'off_time1'       => $this->off_time1,
            'off_time2'       => $this->off_time2,
            'how_call'        => $this->how_call,
            'strategy'        => $this->strategy,
            'check_wbt'       => $this->check_wbt,
            'unload_id'       => $this->unload_id,
            'organization_id' => $this->organization_id,
            'organization_title' => $this->organization->title,
            'ats_group_id'    => $this->ats_group_id,
            'ats_group_name'  => $this->atsGroup->name,
            'organizations'   => $this->organizations,
            'is_work'         => $this->is_work,
            'last_status'     => $this->last_status,
            'dsday'           => $this->dsday,
            'avr'             => $this->avr,
        ];
        
        if ($this->type == 'in') {
            $data['caller_ids'] = $this->callerIdsIn;
            $data['operators'] = $this->callerIdsOper;
        } elseif ($this->type == 'auto') {
            $data['operators'] = $this->callerIdsOper;
        }
        
        if ($this->hst) {
            $data['history'] = $this->hst;
        }
        
        if ($data['operators']) {
            $data['operators'] = $data['operators']->map(function($operator) {
                $last_status = $operator->status->last();
                unset($operator->status);
                $operator->last_name = $operator->atsUser->user->last_name;
                $operator->first_name = $operator->atsUser->user->first_name;
                $operator->middle_name = $operator->atsUser->user->middle_name;
                $operator->organization = $operator->atsUser->user->organization->title;
                $operator->option_in_call = $operator->atsUser->option_in_call;
                unset($operator->atsUser);
                $operator->status_name_en = $last_status->status->name_en;
                $operator->status_name_ru = $last_status->status->name_ru;
                $operator->status = $last_status;
                return $operator;
            });
        }
        
        return $data;
    }
}
