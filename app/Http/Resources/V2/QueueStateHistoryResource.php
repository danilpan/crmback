<?php
namespace App\Http\Resources\V2;

class QueueStateHistoryResource extends Resource
{
    public function toArray($request)
    {
        $data = [
            'id'              => $this->id,
            'queue_id'        => $this->queue_id,
            'main'            => $this->main,
            'online'          => $this->online,
            'speak'           => $this->speak,
            'option_in_calls' => $this->option_in_calls
        ];

        return $data;
    }
}