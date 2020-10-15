<?php

//https://docs.mobizon.com/api/index.html
//https://github.com/mobizon/mobizon-php

namespace App\Services\Sms;

use App\Models\Order;
use App\Models\Sms;
use App\Repositories\SmsRepository;
use App\Services\Service;
use App\Libraries\MobizonApi;
use Transliterate;
use Auth;

class SmsAdapterMobizon
{

    protected $apiData;
    protected $orderData;
    protected $mobizon;
    protected $smsRepository;    

    public function __construct(
        $apiData = [],
        $orderData = [],
        SmsRepository $smsRepository = null                         
    ){
        $this->apiData = $apiData;        
        $this->orderData = $orderData;                                        
        $this->smsRepository = $smsRepository;    
    }
            
    public function send($phone, string $message)
    {
        
        $this->get_mobizon();                 

        if ($this->balance() > 10) {
            return $this->mobizon_send($phone, $message, 1);                          
        } else {
            return 0;
        }
    }

    public function bulkSending(string $api_key = '', array $orders, $type)
    {
        if(!empty($api_key)){
            $this->get_mobizon($api_key);
        }else{
            $this->get_mobizon();
        }

        foreach ($orders as $order) {
            if ($this->balance() > 10) {
                $this->orderData = $order;
                $this->mobizon_send(0, $order['sms_message'], $type);
            }            
        }

        return true;
    }

    /**
     * Добавление сообщения в SMS-центр. Сохранение результата.
     * $phone индекс номера телефона
     * $message сообщение
     * $type тип
     */
    private function mobizon_send($phone, $message, $type)
    {
        
        $phone_number = $this->orderData['phones'][$phone];
        $message = $this->translit($message);
        
        $result = $this->mobizon->call('message',
            'sendSMSMessage',
            array(
                'recipient' => $phone_number,
                'text'      => $message,                
            ), [], true);

        $sms = [];
        $sms['organization_id'] = $this->orderData['organization_id'];
        $sms['order_id'] = $this->orderData['id'];            
        $sms['user_id'] = (isset(Auth::user()->id))?Auth::user()->id:1;
        $sms['sms_provider_id'] = $this->apiData['id'];
        $sms['phone'] = $phone_number;
        $sms['message'] = $message;
        $sms['type'] = $type;        

        if (!empty($result->messageId)) {                
            $sms['status'] = 'send';
            $sms['sms_provider_status'] = 'Передано в смс-центр.';
            $sms['service_id'] = $result->messageId;            
        }elseif (!empty($result->recipient)) {
            $sms['status'] = 'final-error';
            $sms['sms_provider_status'] = $result->recipient;
            $sms['service_id'] = 0;            
        }else{
            $sms['status'] = 'final-error';          
            $sms['sms_provider_status'] = 'Не получен ответ от смс-центра';  
            $sms['service_id'] = 0;          
        }            

        return $this->smsRepository->create($sms);              
    }

    public function balance()
    {
     
        if ($this->mobizon->call('User', 'GetOwnBalance') && $this->mobizon->hasData('balance')) {            
            return $this->mobizon->getData('balance');
        }         
        
    }    

    public function status(string $api_key, $sms_provider_id, array $messageIds)
    {
        
        $this->get_mobizon($api_key);                     
        $messageChunked = array_chunk($messageIds, 100);
        foreach ($messageChunked as $messageArr) {             
            $messageStatus = $this->mobizon->call(
                'message',
                'getSMSStatus',
                array(
                    'ids' => $messageArr
                ),
                array(),
                true
            );            
            if ($this->mobizon->hasData()) {
                foreach ($this->mobizon->getData() as $messageInfo) {                    
                    $smsData = $this->get_status($messageInfo);                     
                    $sms = $this->smsRepository->findWhere(['sms_provider_id'=>$sms_provider_id, 'service_id'=>$messageInfo->id])->first();
                    $this->smsRepository->update($smsData, $sms->id);                     
                }
            }
        }
    }   

    public function get_status($responseResult){
        $arrResult = [];        

        if ('DELIVRD' == $responseResult->status) {
            $arrResult['status'] = 'ok';
            $arrResult['sms_provider_status'] = $responseResult->status;
        }elseif ( ('UNDELIV' == $responseResult->status) 
            || ('REJECTD' == $responseResult->status) 
            || ('EXPIRED' == $responseResult->status)
            || ('DELETED' == $responseResult->status) ) {
                $arrResult['status'] = 'final-error';
                $arrResult['sms_provider_status'] = $responseResult->status;                    
        }elseif ( ('ACCEPTD' == $responseResult->status) 
            || ('PDLIVRD' == $responseResult->status) 
            || ('ENQUEUD' == $responseResult->status)
            || ('NEW' == $responseResult->status) ) {
                $arrResult['status'] = 'wait';
                $arrResult['sms_provider_status'] = $responseResult->status;                    
        }/*elseif($responseResult == null) {
            $arrResult['status'] = 'final-error';
            $arrResult['sms_provider_status'] = 'Этот заказ не должен запрашивать обновление статуса. Так как он не был отправлен';        
        } else {
            $arrResult['status'] = 'final-error';
            $arrResult['sms_provider_status'] = 'Ошибка';
        }*/

        return $arrResult;    
    }    

    public function describe_status($status){
        $STATUSES = [
            'NEW' => 'Новое сообщение, еще не было отправлено',
            'ENQUEUD' => 'Прошло модерацию и поставлено в очередь на отправку',
            'ACCEPTD' => 'Отправлено из системы и принято оператором для дальнейшей пересылки получателю',
            'UNDELIV' => 'Не доставлено получателю',
            'REJECTD' => 'Отклонено оператором по одной из множества причин - неверный номер получателя, запрещенный текст и т.д.',
            'PDLIVRD' => 'Не все сегменты сообщения доставлены получателю, некоторые операторы возвращают отчет только о первом доставленном сегменте, поэтому такое сообщение после истечения срока жизни перейдет в статус DELIVRD',
            'DELIVRD' => 'Доставлено получателю полностью',
            'EXPIRED' => 'Доставка не удалась так как истек срок жизни сообщения (по умолчанию 3 суток)',
            'DELETED' => 'Удалено из-за ограничений и не доставлено до получателя',         
        ]; 

        return $STATUSES[$status];
    }

    private function get_mobizon($api_key=false){

        if(!$api_key){
            $api_data = json_decode($this->apiData['data']);        
            $api_key = $api_data->api;        
        }

        $this->mobizon = new MobizonApi(['apiKey' => $api_key]);  

    }

    static function translit($str, $is_link = false){
        
        if($is_link==true)
        {
            $sim = array (" " =>"-","?"=>"-","й" => "i", "ц" => "ts", "у" => "u", "к" => "k", "е" => "e", "н" => "n", "г" => "g", "ш" => "sh", "щ" => "sh", "з" => "z", "х" => "h", "ъ" => "'", "ф" => "f", "ы" => "y", "в" => "v", "а" => "a", "п" => "p", "р" => "r", "о" => "o", "л" => "l", "д" => "d", "ж" => "dj", "э" => "e", "я" => "ya", "ч" => "ch", "с" => "s", "м" => "m", "и" => "i", "т" => "t", "ь" => "'", "б" => "b", "ю" => "yu", "Й" => "I", "Ц" => "Ts", "У" => "U", "К" => "K", "Е" => "E", "Н" => "N", "Г" => "G", "Ш" => "Sh", "Щ" => "Sh", "З" => "Z", "Х" => "H", "Ъ" => "'", "Ф" => "F", "Ы" => "I", "В" => "V", "А" => "A", "П" => "P", "Р" => "R", "О" => "O", "Л" => "L", "Д" => "D", "Ж" => "Dj", "Э" => "E", "Я" => "Ya", "Ч" => "Ch", "С" => "S", "М" => "M", "И" => "I", "Т" => "T", "Ь" => "", "Б" => "B", "Ю" => "Yu","`" => "'","ё" => "yo","Ё" => "Yo","‎" => ""); 
        }else{
            $sim = array (" " =>" ","?"=>"-","й" => "i", "ц" => "ts", "у" => "u", "к" => "k", "е" => "e", "н" => "n", "г" => "g", "ш" => "sh", "щ" => "sh", "з" => "z", "х" => "h", "ъ" => "'", "ф" => "f", "ы" => "y", "в" => "v", "а" => "a", "п" => "p", "р" => "r", "о" => "o", "л" => "l", "д" => "d", "ж" => "dj", "э" => "e", "я" => "ya", "ч" => "ch", "с" => "s", "м" => "m", "и" => "i", "т" => "t", "ь" => "'", "б" => "b", "ю" => "yu", "Й" => "I", "Ц" => "Ts", "У" => "U", "К" => "K", "Е" => "E", "Н" => "N", "Г" => "G", "Ш" => "Sh", "Щ" => "Sh", "З" => "Z", "Х" => "H", "Ъ" => "'", "Ф" => "F", "Ы" => "I", "В" => "V", "А" => "A", "П" => "P", "Р" => "R", "О" => "O", "Л" => "L", "Д" => "D", "Ж" => "Dj", "Э" => "E", "Я" => "Ya", "Ч" => "Ch", "С" => "S", "М" => "M", "И" => "I", "Т" => "T", "Ь" => "", "Б" => "B", "Ю" => "Yu","`" => "'","ё" => "yo","Ё" => "Yo","‎" => ""); 
        }
        
        return strtr($str, $sim); 
    }

}