<?php
namespace App\Http\Requests\Api\V2;


class OrderUpdateRequest extends Request
{
    public function rules()
    {
        return [
            'client_name' => 'max:200',
            'phones' => 'required|array',
            'full_address' => 'max:255',
            'region'=>'max:255',
            'area'   	=> 'max:255',
            'city'  =>'max:255',
            'street'  =>'max:255',
            'home'  =>'max:255',            
            'room'  =>'max:255',
            'housing'  =>'max:255',
			'postcode'  =>'max:255',
            'warehouse'  =>'max:255',
            'warehouse_id'  =>'max:255',
            'full_address'  =>'max:255',
            'statuses' => 'nullable|array',
            'project_info' => 'nullable|array',
            'goal' => 'nullable|array',
            'delivery_types_id' => 'nullable|exists:delivery_types,id|integer',
            'delivery_types_price' => 'nullable|numeric',
            'surplus_percent_price' => 'sometimes|nullable|numeric',
            'comment' => 'nullable',
            'dial_time' => 'nullable|date',
            'sales' => 'array',
            'delivery_date_finish' => 'nullable|date',
            'delivery_time_1' => 'nullable',
            'delivery_time_2' => 'nullable',
            'age_id' => 'nullable',
            'track_number' => 'nullable',
            'manager_id' => 'nullable',
            'source_id' => 'nullable',
            'device_id' => 'nullable',
            'is_double' => 'nullable',
            'is_unload' => 'nullable',
            'order_sender_id' => 'nullable'

        ];
    }
}
