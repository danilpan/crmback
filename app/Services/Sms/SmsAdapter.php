<?php
namespace App\Services\Sms;

use App\Repositories\OrdersRepository;
use App\Repositories\GeoRepository;
use App\Repositories\SmsRepository;
use App\Models\SmsRule;
use Exception;    

class SmsAdapter
{
    const MOBIZON_GEO = ['KZ', 'KG', 'UZ', 'AM'];
    const SMSC_GEO    = ['RU', 'BY', 'AZ'];  

    protected $ordersRepository;
    protected $geoRepository; 
    protected $smsRepository;  

    public function __construct(
        OrdersRepository $ordersRepository,
        GeoRepository $geoRepository,
        SmsRepository $smsRepository  
    ){
        $this->ordersRepository = $ordersRepository;
        $this->geoRepository = $geoRepository;        
        $this->smsRepository = $smsRepository;  
        $this->apiData = [];
        $this->orderData = [];
    }

    /**
     * @param $orderGeo
     * @return SmsAdapterMobizon|SmsAdapterSmsc
     * @throws Exception
     */
    public function create($key, $type)
    {
        if ($this->isMobison($key, $type)) {
            return new SmsAdapterMobizon($this->apiData, $this->orderData, $this->smsRepository);
        } elseif ($this->isSmsc($key, $type)) {
            return new SmsAdapterSmsc($this->apiData, $this->orderData, $this->smsRepository);
        }

        //throw new Exception('unknown geo');

        return false;
    }  

    protected function isMobison($key, $type)
    {
        return $this->check($key, $type, 1);
    }

    protected function isSmsc($key, $type)
    {
        return $this->check($key, $type, 2);
    }

    public function isOrder(Order $order)
    {
        return $order;
    }

    public function status_sms_crm($status_crm){
        $sms_statuses = [
            'ok' => 'Сообщение доставлено',
            'wait' => 'Принято смс-центром, но пока не доставлено',
            'final-error' => 'Ошибка. Смс не принято смс-центром',
            'send' => 'Принято смс-центром'
        ];
        return $sms_statuses[$status_crm];
    }

    public function check($key, $type, $sms_provider_id)
    {
        
        $order_info = $this->ordersRepository->searchByParams(
            ['match' => [
                'key' => $key                ]
            ], 
            ['key'=>'asc']
        )->toArray();        

        if(empty($order_info))return false;

        $this->orderData = $order_info[0];

        $geo_info = $this->geoRepository->findBy('code', $order_info[0]['country_code']);     

        $check['organization_id'] = $order_info[0]['organization_id'];
        $check['is_work'] = 1;             

        $smsRules = SmsRule::where($check)->with('sms_provider')->get();                

        foreach ($smsRules as $rule) {
            $this->apiData = $rule->getRelation('sms_provider');            
            if($this->apiData['sms_provider']!=$sms_provider_id)continue;
            if($rule['geo_id']==$geo_info['id']){                                    
                if(is_null($rule['type'])){                    
                    return true;
                }elseif($rule['type'] == $type){                                            
                    return true;
                }
            }                        

        }

        return false;
        
    }

    public function getMobizon($apiData = [], $orderData = [])
    {      
        return new SmsAdapterMobizon($apiData, $orderData, $this->smsRepository);     
    }

    public function getSmsc($apiData = [], $orderData = [])
    {        
        return new SmsAdapterSmsc($apiData, $orderData, $this->smsRepository);       
    }

    //public abstract function send(Order $order, string $message);
    //public abstract function bulkSending(array $orders, string $message);
    //public abstract function balance();
}