<?php
namespace App\Observers;

use App\Models\User;
use App\Traits\TracksHistoryTrait;

class UserObserver
{
    use TracksHistoryTrait;

    /**
     * Handle to the order "created" event.
     *
     * @param User $user
     * @return void
     */
    public function created(User $user)
    {
        $this->track($user);
    }

    /**
     * Handle the status "updated" event.
     *
     * @param User $user
     * @return void
     */
    public function updated(User $user)
    {
        $this->track($user);
    }

    /**
     * Handle the status "deleted" event.
     *
     * @param User $user
     * @return void
     */
    public function deleted(User $user)
    {
        //
    }
}