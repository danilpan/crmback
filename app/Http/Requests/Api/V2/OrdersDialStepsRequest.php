<?php
namespace App\Http\Requests\Api\V2;


class OrdersDialStepsRequest extends Request
{
    public function rules()
    {
        return [
            'queue_id' => 'required|integer',
            'order_id' => 'required|integer',
            'dial_step' => 'required|integer',
            'dial_time' => 'required|date'
        ];
    }
}
