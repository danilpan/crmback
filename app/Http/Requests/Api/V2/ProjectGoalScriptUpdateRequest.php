<?php
namespace App\Http\Requests\Api\V2;


class ProjectGoalScriptUpdateRequest extends Request
{
    public function rules()
    {
        return [        	
	        'name' => 'max:255',		    		    
		    'file' => 'file|mimes:htm,html',
		    'status' => 'string',
		    'cross_sales' => 'json'
        ];
    }
}
 