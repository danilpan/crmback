<?php


namespace App\Helpers;
use Request;
use Auth;
use App\Models\LogActivity as LogActivityModel;

class LogActivity
{   

    public static function addToLog($action, $info = [])
    {
    	//dd(Request::server('HTTP_REFERER'));
        $log = [];
    	$log['action'] = $action;
    	$log['url'] = urldecode(Request::url());
        $log['referer'] = urldecode(Request::server('HTTP_REFERER'));
    	$log['method'] = Request::method();
    	$log['ip'] = Request::ip();
    	$log['user_agent'] = Request::header('user-agent');
    	$log['user_id'] = (isset(Auth::user()->id)) ? Auth::user()->id : 1;
        $log['info'] = json_encode($info, JSON_UNESCAPED_UNICODE);
        //dd($log);
    	LogActivityModel::create($log);        
    }


    public static function logActivityLists()
    {
    	return LogActivityModel::latest()->get();
    }


}