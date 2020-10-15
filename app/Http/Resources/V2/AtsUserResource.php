<?php

namespace App\Http\Resources\V2;


class AtsUserResource extends Resource
{
    public function toArray($request)
    {
        $data   = [
            'id'             => $this->id,
            'port'           => $this->port,
            'passwd'         => $this->passwd,
            'login'          => $this->login,
            'max_channels'   => $this->max_channels,
            'is_work'        => $this->is_work,
            'type'           => $this->type,
            'ats_group_id'   => $this->atsGroup->id,
            'comment'        => $this->comment,
            'out_calls'      => $this->out_calls, 
            'option_in_call' => $this->option_in_call,
        ];
        
        if ($this->status) {
            $data['status'] = $this->status->where('ats_user_id', $this->id)->first();
        }
        
        if ($this->user) {
            $data['user_id'] = $this->user->id;
        }
        
        if ($this->hst) {
            $data['history'] = OrderHistoryResource::collection($this->hst);
        }
        
        if ($this->user_id) {
            $data['user'] = $this->user;
        }
        
        return $data;
    }
}
