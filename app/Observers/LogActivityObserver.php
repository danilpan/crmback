<?php

namespace App\Observers;

use App\Models\LogActivity;
use App\Repositories\LogActivityRepository;


class LogActivityObserver
{    
    protected $logActivityRepository;   

    public function __construct(        
        LogActivityRepository $LogActivityRepository        
    )
    {
        $this->repository = $LogActivityRepository;        
    }
    /**
     * Handle to the order "created" event.
     *
     * @param  \App\LogActivity  $log_activity
     * @return void
     */
    public function created(LogActivity $log_activity)
    {
        //
        $this->repository->reindexModel($log_activity,true);
    }

    /**
     * Handle the log_activity "updated" event.
     *
     * @param  \App\LogActivity  $log_activity
     * @return void
     */
    public function updated(LogActivity $log_activity)
    {
        //
    }

    /**
     * Handle the log_activity "deleted" event.
     *
     * @param  \App\LogActivity  $log_activity
     * @return void
     */
    public function deleted(LogActivity $log_activity)
    {
        //
    }
}
