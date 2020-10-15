<?php
namespace App\Http\Requests\Api\V2;


class ProjectGoalRequest extends Request
{
    public function rules()
    {
        return [
        'project_id' => 'required|integer',
        'name' => 'required|max:255',
	    'call_center_id' => 'required|integer',
	    'geo_id' => 'required|integer',
	    'price' => 'required|numeric',
	    'price_currency_id' => 'required|integer',
	    'action_payment' => 'required|numeric',
	    'action_payment_currency_id' => 'required|integer',
	    'web_master_payment' => 'required|numeric',
	    'web_master_payment_currency_id' => 'required|integer',
	    'is_private' => 'required',
	    'additional_payment' => 'numeric',
	    'additional_payment_currency_id' => 'required|integer',
        'min_price' => 'numeric',
        'max_price' => 'numeric'
        ];
    }
}
