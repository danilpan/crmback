<?php

namespace App\Services;

use App\Models\SmsTemplate;
use App\Repositories\OrdersRepository;
use App\Services\Sms\SmsAdapter;
use App\Models\Order;

class SmsService extends SmsAdapter
{
	
	public function checkKeys(string $string)
    {
        if (!empty($string)) {

			$arrayId = explode(',', $string);
			
			$arrayItemsId = [];
			foreach ($arrayId as $id) {	
				
				$itemId = trim($id);

				if ($itemId !== '') {
					$arrayItemsId[] = $itemId;
				}

			}

			return array_unique($arrayItemsId);			

		}

    }

    public function prepareSms($sms, $permission_list = null){
    	$sms = $sms->map(function($item) use ($permission_list) {
    		if($permission_list != null && !$permission_list["menu.main.orders.view_phone_number"]){
	    		$new_phone='';
	            for($i = 0; $i < strlen($item['phone']);  $i++){
	            	if($i>strlen($item['phone'])-6){
	                	$new_phone .= '?';
	                }else{
	                	$new_phone .= $item['phone'][$i];
	                }
	            }
	            $item['phone'] = $new_phone;
        	}
        	$item['type'] = ($item['type']==1)?'Ручная':'Автоматическая';
        	$item['status'] = $this->status_sms_crm($item['status']);
            return $item;
        });
        return $sms;
    }

    public function smsBuilder($template_id, $order_key, OrdersRepository $ordersRepository)
    {
        $template = SmsTemplate::find($template_id);
        if($template) {
            $order_info = $ordersRepository->searchByParams(
                ['match' => [
                    'key' => $order_key]
                ],
                ['key' => 'asc']
            )->toArray();

            $order = $order_info[0];
            $client_name = $order['client_name'];
            $track_number = $order['track_number'];
            $project = $order['projects'];
            $site = null;
            $order_key = $order['key'][0];
            $order_id = $order['id'];

            if ($project) {
                $project = $project[0]['name_for_client'];
            } else{
                $project = null;
            }
            if($order['project_page']) {
                $site = $order['project_page'][0]['link'];
            }elseif($project){
                if($order['projects'][0]['import_id']) {
                    $import_id = explode('reklpro_id_', $order['projects'][0]['import_id'])[1];
                    $json = json_decode(file_get_contents("https://rekl.pro/api_get_offers/get_offer_info?key=asdjkijn34nn88234bjjngjll33356n&offer_id=$import_id"), true);
                    $site = $json['url'];
                }
            }

            $template_text = $template->sms_text;

            if ((str_contains($template_text, '{{site}}') && empty($site))
                || (str_contains($template_text, '{{project}}') && empty($project))
                || (str_contains($template_text, '{{client_name}}') && empty($client_name))
                || (str_contains($template_text, '{{track_number}}') && empty($track_number))) {
                return null;
            }

            $template_text = str_replace('{{client_name}}', $client_name, $template_text);
            $template_text = str_replace('{{site}}', $site, $template_text);
            $template_text = str_replace('{{project}}', $project, $template_text);
            $template_text = str_replace('{{track_number}}', $track_number, $template_text);
            $template_text = str_replace('{{order_id}}', $order_id, $template_text);
            $template_text = str_replace('{{order_key}}', $order_key, $template_text);

            return $template_text;
        }
        else{
            return null;
        }
    }

}
