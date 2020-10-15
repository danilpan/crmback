<?php
namespace App\Http\Requests\Api\V2;


class SmsProviderRequest extends Request
{
    public function rules()
    {
        $rules = [];

        $rules['name'] = 'required|max:180';
        $rules['sms_provider'] = 'required|integer|max:2';
        
        $sms_provider = $this->input('sms_provider');


        if($sms_provider==1){ //Mobizon
			$rules['data.api'] = 'required|min:32';        	
        }elseif($sms_provider==2){ //Smsc
        	$rules['data.login'] = 'required|max:32';        	
        	$rules['data.password'] = 'required|max:32';        	        	
        	$rules['data.from'] = 'required|email'; 
        }

        return $rules;
    }
}
