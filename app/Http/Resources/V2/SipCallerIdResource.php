<?php

namespace App\Http\Resources\V2;


class SipCallerIdResource extends Resource
{
    public function toArray($request)
    {
        $data   = [
            'id'           => $this->id,
            'sip_id'       => $this->sip_id,
            'ats_user_id'  => $this->ats_user_id,
            'caller_id'    => $this->caller_id,
            'ats_queue_id' => $this->ats_queue_id,
            'status'       => $this->status,
        ];
        
        if ($this->ats_user_id) {
            $data['ats_user'] = $this->atsUser;
            if ($this->atsUser->user) {
                $data['ats_user']['user'] = $this->atsUser->user;
                if ($this->atsUser->user->organization) {
                    $data['ats_user']['user']['organization'] = $this->atsUser->user->organization;
                }
            }
        }
        
        if ($this->sip_id) {
            $data['sip'] = $this->sip;
        }

        return $data;
    }
}
