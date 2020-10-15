<?php
namespace App\Repositories;

use App\Models\SipCallerId;

class SipCallerIdRepository extends Repository
{
    public function model()
    {
        return SipCallerId::class;
    }
    
    public function prepareSearchData($model)
    {
        $data   = [
            'id'           => $model->id,
            'sip_id'       => $model->sip_id,
            'ats_user_id'  => $model->ats_user_id,
            'caller_id'    => $model->caller_id,
            'ats_queue_id' => $model->ats_queue_id,
        ];
        
        return $data;
    }
}