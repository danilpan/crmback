<?php

namespace App\Services\Sms;

use App\Models\Order;
use App\Services\Service;
use App\Repositories\SmsRepository;
use App\Libraries\Smsc;
use Transliterate;
use Auth;

class SmsAdapterSmsc
{

    protected $apiData;
    protected $orderData;
    protected $smsc;
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
        $this->get_smsc(); 

        if ($this->balance() > 10) {
            return $this->smsc_send($phone, $message, 1);                        
        } else {
            return 0;
        }
    }

    public function bulkSending(array $api_key = [], array $orders, $type)
    {
       if(!empty($api_key)){
            $this->get_smsc($api_key);
        }else{
            $this->get_smsc();
        }

       foreach ($orders as $order) {
            if ($this->balance() > 10) {
                $this->orderData = $order;
                $this->smsc_send(0, $order['sms_message'], $type);
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
    private function smsc_send($phone, $message, $type)
    {
        
        $phone_number = $this->orderData['phones'][$phone];
        $message = $this->translit($message);        
        
        $result = $this->smsc->send_sms($phone_number, $message);        

        $result = [100,'-8'];
        
        $sms = [];
        $sms['organization_id'] = $this->orderData['organization_id'];
        $sms['order_id'] = $this->orderData['id'];            
        $sms['user_id'] = (isset(Auth::user()->id))?Auth::user()->id:1;
        $sms['sms_provider_id'] = $this->apiData['id'];
        $sms['phone'] = $phone_number;
        $sms['message'] = $message;
        $sms['type'] = $type;        
        $sms['service_id'] = $result[0];            
        $sms['sms_provider_status'] = $result[1];

        if (4 == count($result)) {                      
            $sms['status'] = 'send';
            $sms['price'] = $result[2];        
        } elseif (2 == count($result)) {            
            $sms['status'] = 'final-error';        
        }

        return $this->smsRepository->create($sms);    

    }    

    public function status(array $api_key, $sms_provider_id,  array $messageIds)
    {
        
        $this->get_smsc($api_key);        

        $ids = array_chunk($messageIds['ids'], 100);
        $phones = array_chunk($messageIds['phones'], 100);

        foreach ($ids as $key => $ids_block) {
            $ids_str = '';
            $phones_str = '';

            if(count($ids_block)!=1){
                $ids_str = implode(",", $ids_block);
                $phones_str = implode(",", $phones[$key]);
            }else{
                $ids_str = $ids_block[0].',';
                $phones_str = $phones[$key][0].',';
            }               

            $result = $this->smsc->get_status($ids_str, $phones_str);              

            if (!empty($result)) {               
                foreach ($result as $res) {
                    
                    if (in_array($res[0], ['-3','-1','0','1','2','3','20','22','23','24','25'])) {                    
                        $smsData['sms_provider_status'] = $res[0];

                        if ('1' == $res[0]) {
                            $smsData['status'] = 'ok';                        
                        }

                        if ( ('-3' == $res[0]) 
                            || ('3' == $res[0])
                            || ('20' == $res[0])
                            || ('22' == $res[0])
                            || ('23' == $res[0])
                            || ('25' == $res[0]) ) {
                            $smsData['status'] = 'final-error';                       
                        }


                        $sms = $this->smsRepository->findWhere(['sms_provider_id'=>$sms_provider_id, 'service_id'=>$res[3]])->first();   ;

                        $this->smsRepository->update($smsData, $sms->id);                                    
                    }                    
                }             
            }
        }      
        
    } 

    public function balance()
    {
        return $this->smsc->get_balance();
    }

    public function describe_send_status($status){
        $STATUSES = [
            '-1' => 'Ошибка в параметрах.',
            '-2' => 'Неверный логин или пароль.',
            '-3' => 'Недостаточно средств на счете Клиента.',
            '-4' => 'IP-адрес временно заблокирован из-за частых ошибок в запросах. Подробнее',
            '-5' => 'Неверный формат даты.',
            '-6' => 'Сообщение запрещено (по тексту или по имени отправителя).',
            '-7' => 'Неверный формат номера телефона.',
            '-8' => 'Сообщение на указанный номер не может быть доставлено.',
            '-9' => 'Отправка более одного одинакового запроса на передачу SMS-сообщения либо более пяти одинаковых запросов на получение стоимости сообщения в течение минуты.
            Данная ошибка возникает также при попытке отправки более 15 любых запросов одновременно.',        
        ]; 

        return $STATUSES[$status];
    }

    public function describe_check_status($status){
        $STATUSES = [
            '-3' => 'Сообщение не найдено',
            '-1' => 'Ожидает отправки',
            '0' => 'Передано оператору',
            '1' => 'Доставлено',
            '2' => 'Прочитано',
            '3' => 'Просрочено',
            '20' => 'Невозможно доставить',
            '22' => 'Неверный номер',
            '23' => 'Запрещено',
            '24' => 'Недостаточно средств',
            '25' => 'Недоступный номер',
        ]; 

        return $STATUSES[$status];
    }

    private function get_smsc($api_key=false){

        if(!$api_key){
            $api_key = [];
            $api_data = json_decode($this->apiData['data']);        
            $api_key['login'] = $api_data->login;        
            $api_key['password'] = $api_data->password;        
            $api_key['from'] = $api_data->from; 
        }

        $this->smsc = new Smsc($api_key);  



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