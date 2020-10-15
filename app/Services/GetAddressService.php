<?php
namespace App\Services;

use App\Models\User;
use Config;

class GetAddressService extends Service
{
    public function __construct()
    {
        
    }

    public function GetAddress($str, $geo = false)
    {
   
		if($geo){
			if($geo=='RU'){
				// данные для запроса
					$postData = [
						'query' => $str,
						'content' => 5,
						'from_bound' => [
							'value' => 'city'
						]
					];
					return $this->get_sock($postData);
				}else if($geo=='KZ'){
					$str = urlencode(trim($str));									
					$address_json =  file_get_contents('https://api.post.kz/api/byAddress/'.$str.'?from=0');
					$address_obj = json_decode($address_json);
					$address = [];
					$arr = [];											
					for ($i=0; $i < 6; $i++) { 					
						$address[$i]['addressRus'] = $address_obj->data[$i]->addressRus;
						$address[$i]['addressKaz'] = $address_obj->data[$i]->addressKaz;
						$address[$i]['postcode'] = $address_obj->data[$i]->fullAddress->postcode;
						$address[$i]['id'] = $i;
						$arr = explode(',', $address[$i]['addressRus']);						
						if(count($arr)==4){
							$address[$i]['region'] = $arr[0];
							$address[$i]['city'] = $arr[1];
							$address[$i]['street'] =  preg_replace('/улица|\ |УЛИЦА/','',$arr[2]);
							$address[$i]['home'] = preg_replace('/([\W]+)/','',$arr[3]);
						}else if(count($arr)==5){
							$address[$i]['region'] = $arr[0];
							$address[$i]['city'] = $arr[1];
							$address[$i]['street'] = preg_replace('/улица| |УЛИЦА/','',$arr[3]);
							$address[$i]['home'] = preg_replace('/([\W]+)/','',$arr[4]);
						}						
					}
					unset($address_obj);
					return $address;	
				}  else if($geo=='UA'){
                    $str = trim($str);
                    $address_get = json_decode($this->get_new_post($str,'address'),true);
                    if($address_get['success']==false)return [];
                    if ($address_get['data'][0]['TotalCount']==0) {
                    	$str = str_replace(array("И","и"),array("І","і"),$str);
                    	$address_get = json_decode($this->get_new_post($str,'address'),true);
                    }
                	$address_data = $address_get['data'][0]['Addresses'];
                    $address = [];
                    $arr = [];     


                    for ($i=0; $i < 6; $i++) { 
                        $address[$i]['id'] = $i;
                        $address[$i]['addressRus'] = $this->uk_symbol_del( $address_data[$i]['Present'] );
                        $address[$i]['addressKaz'] = $this->uk_symbol_del( $address_data[$i]['Present'] );
                        $address[$i]['region'] = $this->uk_symbol_del( $address_data[$i]['ParentRegionCode'].' '.$address_data[$i]['Area'] );
                        $address[$i]['area'] = $this->uk_symbol_del( $address_data[$i]['RegionTypesCode'].' '.$address_data[$i]['Region'] );
                        $address[$i]['city'] = $this->uk_symbol_del( $address_data[$i]['SettlementTypeCode'].' '.$address_data[$i]['MainDescription'] );
                        $address[$i]['warehouse'] = $this->uk_symbol_del( $address_data[$i]['DeliveryCity'] );
                        $address[$i]['postcode'] = '';
                        $address[$i]['street'] = '';
                        $address[$i]['home'] = '';
                        if(($i+1) >= count($address_data))break;
                    }
                    return $address;
                }

		}
    }   

    public function get_sock($postData){ 		
		$d = json_encode($postData);			
		$requestURL = "https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address";				
		$ch = curl_init($requestURL);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			"Content-Type: application/json",
			"Accept: application/json",
			"Authorization: Token 9027ab1c327fc95d5844c17f437149fb5cc1623b"
		]);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $d);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$response = curl_exec($ch);
		curl_close($ch);
				
		$data = json_decode($response,true);
		$address = array();		
		
		for ($i=0; $i < 6; $i++) { 
			if($data['suggestions'][$i]['data']['region_type_full']==NULL){
				$region = $data['suggestions'][$i]['data']['region'];				
			}else{				
				$region = $data['suggestions'][$i]['data']['region_type_full'] . ' ' .$data['suggestions'][$i]['data']['region'];				
			}
			if($data['suggestions'][$i]['data']['city']==NULL){						
				if($data['suggestions'][$i]['data']['settlement_type_full']!==NULL&&$data['suggestions'][$i]['data']['settlement']!==NULL){		
					$city = $data['suggestions'][$i]['data']['settlement_type_full']. ' ' .$data['suggestions'][$i]['data']['settlement'];		
				}else{							
					$city = $data['suggestions'][$i]['data']['area_type_full']. ' ' .$data['suggestions'][$i]['data']['area'];					
				}
			}else{				
				$city = $data['suggestions'][$i]['data']['city_type_full']. ' ' .$data['suggestions'][$i]['data']['city'];				
			}
			$street = $data['suggestions'][$i]['data']['street'];					
			$home = $data['suggestions'][$i]['data']['house'];		
			$address[$i]['addressRus'] = $data['suggestions'][$i]['value'];
			$address[$i]['addressKaz'] = $data['suggestions'][$i]['value'];
			$address[$i]['postcode'] = $data['suggestions'][$i]['data']['postal_code'];
			$address[$i]['id'] = $i;		
			$address[$i]['region'] = $region;
			$address[$i]['area'] = $data['suggestions'][$i]['data']['area'];
			$address[$i]['city'] = $city;
			$address[$i]['street'] = $street;
			$address[$i]['home'] = $home;
			if(($i+1) >= count($data['suggestions']))break;
		}

		return $address;
	}

	public function get_new_post($msg, $method){
	    if($method == 'address'){
		    $options = [
		    'modelName' => 'Address',
		    'calledMethod' => 'searchSettlements',
		   	'methodProperties' => [
				'CityName'=> $msg,
				'Language'=> 'ru',
				"Limit"=> 7
		    ]
		    ];
	    }elseif($method == 'warehouse'){
		    $options = [
			    'modelName' => 'AddressGeneral',
			    'calledMethod' => 'getWarehouses',
			    'methodProperties' => [			  
			    'CityRef'=> $msg,
			    'Language'=> 'ru'
			    ]
		    ];
	    }

	    $options['apiKey'] = '3c8490b80fc8150299015e741c892f2b';

	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, 'https://api.novaposhta.ua/v2.0/json/');
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	    curl_setopt($ch, CURLOPT_TIMEOUT, 10); 
	    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($options));
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, [
	    	"Content-Type:application/json"
	    ]); 
	    $return= curl_exec($ch);
	    curl_close($ch);
	    return $return;             
    }

    public function select_warehouse($str){

	    $str = trim($str);
	    $address_get = json_decode($this->get_new_post($str,'warehouse'),true);
	    $address_data = $address_get['data'];
	    $address = [];

	    for ($i=0; $i < count($address_data); $i++) { 
	    	$address[$i]['id'] = $i;
	    	$Description = ($address_data[$i]['DescriptionRu'])? $address_data[$i]['DescriptionRu'] : $address_data[$i]['Description'];
	        $address[$i]['Description'] = $Description;
	        $address[$i]['Ref'] = [
	        	'Ref' => $address_data[$i]['Ref'],
	        	'Description' => $Description
	        ];
	    }
	    return $address;
    
    }

    public function uk_symbol_del($str){
	    $str = str_replace(["'"],[""],$str);
	    return $str;
	}

	protected function getSearchRepository()
    {
        return [];
    }    

	protected function addSearchConditions(User $user=null,array $filters=null)
    {
        return $filters;
    }

    public function getPermissionQuery(){
        return $this->permissionQuery;
    }

    public function getExportToExcelLib(){
        return $this->exportToExcel;
    }

}