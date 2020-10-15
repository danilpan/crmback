<?php
namespace App\Repositories;

use App\Models\QueueStateHistory;

class QueueStateHistoryRepository extends Repository
{
    public function model()
    {
        return QueueStateHistory::class;
    }

    public function getMappings()
    {
        $mappings   = [
            'id'    => [
                'type'  => 'integer',
            ],
        ];

        return $mappings;
    }

    public function prepareSearchData($model)
    {
        $data = [
            'id'              => $model->id,
            'queue_id'        => $model->queue_id,
            'main'            => $model->main,
            'online'          => $model->online,
            'speak'           => $model->speak,
            'option_in_calls' => $model->option_in_calls
        ];

        return $data;
    }
}