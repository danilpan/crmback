<?php
namespace App\Http\Requests\Api\V2;


class ProjectGoalScriptRequest extends Request
{
    public function rules()
    {
        return [
        	'project_goal_id' => 'required|integer',
	        'name' => 'required|max:255',		    		    
		    'file' => 'required|file|mimes:htm,html'
        ];
    }
}
 