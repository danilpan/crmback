<?php
namespace App\Http\Resources\V2;

class SmsTemplateResource extends Resource
{
    public function toArray($request)
    {
        $data = [
            'id'              => $this->id,
            'name'            => $this->name,
            'sms_text'        => $this->sms_text,
            'is_work'         => $this->is_work,
            'organizations'   => []
        ];

        if($this->organizations) {
            foreach ($this->organizations as $organization) {
                $data['organizations'][] = [
                    'id' => $organization->id,
                    'title' => $organization->title
                ];
            }
        }

        return $data;
    }
}