<?php

namespace App\Observers;

use App\Models\Status;
use App\Traits\TracksHistoryTrait;


class StatusObserver
{
    use TracksHistoryTrait;
    /**
     * Handle to the order "created" event.
     *
     * @param  \App\Status  $status
     * @return void
     */
    public function created(Status $status)
    {
        //
    }

    /**
     * Handle the status "updated" event.
     *
     * @param  \App\Status  $status
     * @return void
     */
    public function updated(Status $status)
    {
        $this->track($status);                
    }

    /**
     * Handle the status "deleted" event.
     *
     * @param  \App\Status  $status
     * @return void
     */
    public function deleted(Status $status)
    {
        //
    }
}
