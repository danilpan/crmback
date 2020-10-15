<?php

namespace App\Observers;

use App\Models\Order;
use App\Traits\TracksHistoryTrait;


class OrderObserver
{
    use TracksHistoryTrait;
    /**
     * Handle to the order "created" event.
     *
     * @param  \App\Order  $order
     * @return void
     */
    public function created(Order $order)
    {
        $this->track($order);        
    }

    /**
     * Handle the order "updated" event.
     *
     * @param  \App\Order  $order
     * @return void
     */
    public function updated(Order $order)
    {
        if(isset($order->dial_time))
            $order->dial_time = date("Y-m-d H:i:s", strtotime($order->dial_time));                
        $this->track($order);        
        // or
        /*$this->track($order, function ($value, $field) {
            if ($field === 'total') {
                $value /= 100;
            }
            return [
                'body' => "Updated {$field} to ${value}",
            ];
        });*/
    }

    /**
     * Handle the order "deleted" event.
     *
     * @param  \App\Order  $order
     * @return void
     */
    public function deleted(Order $order)
    {
        //
    }
}
