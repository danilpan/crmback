<?php

namespace App\Http\Resources\V2;


class ProjectGoalResource extends Resource
{
    public function toArray($request)
    {
        $data   = [
            'id'    => $this->id,
            'project_id'    => $this->project_id,
            'name' => $this->name,
            'call_center_id' => $this->call_center_id,
	    'geo_id' => $this->geo_id,
	    'price' => $this->price,
	    'price_currency_id' => $this->price_currency_id,
	    'action_payment' => $this->action_payment,
	    'action_payment_currency_id' => $this->action_payment_currency_id,
	    'web_master_payment' => $this->web_master_payment,
	    'web_master_payment_currency_id' => $this->web_master_payment_currency_id,
	    'is_private' => $this->is_private,
	    'additional_payment' => $this->additional_payment,
	    'additional_payment_currency_id' => $this->additional_payment_currency_id,
        'min_price' => $this->min_price,
        'max_price' => $this->max_price
        ];

        return $data;
    }
}
