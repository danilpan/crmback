<?php

namespace App\Helpers;

class Calls
{   

    public static function convertCallStatus($status)
    {
    	$status_names = [
            'addmember' => 'Клиент добавлен в очередь.',
            'agentdump'    => 'Агент сбросил звонящего во время прослушивания приглашения очереди.',
            'agentlogin'  => 'Агент залогинился. Канал записан.',
            'agentlogoff' => 'Агент вышел.',
            'completeagent'   => 'Звонок был завершен агентом.',
            'completecaller'  => 'Звонок был завершен абонентом.', 
            'configreload'    => 'Конфигурация была перезагружена',
            'connect' => 'Абонент был соединен с агентом.',
            'enterqueue'   => 'Звонок пришел в очередь.',
            'exitempty'    => 'Абонент вышел из очереди потому что в очереди не было доступных агентов',
            'exitwithkey'  => 'Абонент нажал кнопку выхода из очереди.',
            'exitwithtimeout' => 'Абонент был выброшен из очереди по таймауту(очень долго ждал).',
            'ringnoanswer'    => 'Оператор не отвечает',
            'ringcanceled'    => 'Оператор сбросил',
            'abandon' => 'Клиент ушел'
        ];
        return (isset($status_names[$status]))?$status_names[$status]:'';  
    }


    public static function convertDisposition($data)
    {
        $disposition = [];
        $temp_statuses = [];
        $disposition['name'] = 'Статус неизвестен';
        $disposition['class'] = 'warning';
        $call_statuses = $data['call_statuses']->toArray();

        if(count($call_statuses)){            
            foreach ($call_statuses as $call_status) {                            
                $temp_statuses[$call_status['status']] = '1';
            }        
        }   

        if($data['call_type']=='out')
        {
            if($data['disposition']=='busy')
            {
                    $disposition['name'] = 'Клиент занят';
                    $disposition['class'] = 'warning';
                }else if($data['disposition']=='no answer'){
                    $disposition['name'] = 'Не отвечен';
                    $disposition['class'] = 'danger';
                }else if($data['disposition']=='answered'){                    
                    $disposition['name'] = 'Отвечен';
                    $disposition['class'] = 'info';                   
                }else if($data['disposition']==''){
                    $disposition['name'] = 'Разговор не окончен';
                    $disposition['class'] = 'warning';
                }else{
                    $disposition['name'] = 'Не отвечен';
                    $disposition['class'] = 'danger';
                }
            }else if($data['call_type']=='auto_reverse')
            {
                if($data['disposition']=='busy')
                {
                    $disposition['name'] = 'Клиент занят';
                    $disposition['class'] = 'warning';        
                }else if($data['disposition']=='no answer'){
                    $disposition['name'] = 'Не отвечен';
                    $disposition['class'] = 'danger';        
                }else if($data['disposition']=='answered'){
                    $disposition['name'] = 'Отвечен';
                    $disposition['class'] = 'info';                  
                }else if($data['disposition']==''){        
                    $disposition['name'] = 'Разговор не окончен';
                    $disposition['class'] = 'warning';        
                }else{
                    $disposition['name'] = 'Отвечен';
                    $disposition['class'] = 'info';                   
                }
            }else if($data['call_type']=='in')
            {
                if(isset($temp_statuses['connect'])||(isset($temp_statuses['completeagent'])||isset($temp_statuses['completecaller'])))
                {
                    $disposition['name'] = 'Отвечен';
                    $disposition['class'] = 'info';                    

                }else if(!isset($temp_statuses['connect']) && isset($temp_statuses['ringnoanswer']))
                {
                    $disposition['name'] = 'Оператор не ответил';
                    $disposition['class'] = 'danger';
                }else if(!isset($temp_statuses['connect']) && isset($temp_statuses['abandon']))
                {
                    $disposition['name'] = 'Не было свободных операторов';
                    $disposition['class'] = 'danger';
                }else if($data['disposition']=='answered'){
                    $disposition['name'] = 'Отвечен';
                    $disposition['class'] = 'info';                   
                }else{
                    $disposition['name'] = 'Статус неизвестен';
                    $disposition['class'] = 'warning';
                }
            }else if($data['call_type']=='auto') 
            {
                if(!isset($temp_statuses['connect']) && isset($temp_statuses['ringnoanswer']) && isset($temp_statuses['abandon']))
                {
                    $disposition['name'] = 'Оператор не ответил';
                    $disposition['class'] = 'danger';
                }else if(!isset($temp_statuses['connect']) && isset($temp_statuses['ringcanceled']) && isset($temp_statuses['abandon']))
                {
                    $disposition['name'] = 'Оператор сбросил';
                    $disposition['class'] = 'warning';
                }else if(($data['disposition']=='busy') && !isset($temp_statuses['connect']))
                {
                    $disposition['name'] = 'Клиент сбросил';
                    $disposition['class'] = 'warning';
                }else  if(($data['disposition']=='no answer') && !isset($temp_statuses['connect']))
                {
                    $disposition['name'] = 'Клиент не берет';
                    $disposition['class'] = 'warning';
                }else if(isset($temp_statuses['connect'])||((isset($temp_statuses['completeagent']))||(isset($temp_statuses['completecaller']))))
                {
                    $disposition['name'] = 'Отвечен';
                    $disposition['class'] = 'info';                   
                }else if((!isset($temp_statuses['connect']))&&(isset($temp_statuses['abandon'])))
                {
                    $disposition['name'] = 'Не было свободных операторов';
                    $disposition['class'] = 'danger';
                }else if($data['disposition']=='failed'){
                    $disposition['name'] = 'Отключен или ошибка';
                    $disposition['class'] = 'danger';
                }else if(($data['disposition']=='answered')&&(!isset($temp_statuses['connect']))){
                    $disposition['name'] = 'Не было свободных операторов';
                    $disposition['class'] = 'danger';
                }else if($data['disposition']=='answered'){
                    $disposition['name'] = 'Отвечен';
                    $disposition['class'] = 'info';                   
                }else{
                    $disposition['name'] = 'Статус неизвестен';
                    $disposition['class'] = 'warning';
                }
            }

        return $disposition;
    }

    public static function getLink($time, $id, $ats_group_id) {                                 // FIXME: Проверка $ats_group_id нужна
        $time = $time ? $time : \Carbon\Carbon::now();                                          // пока в базе есть звонки без группы,
                                                                                                // так-как раньше этого поля не было
        if (strtotime($time->format('Y-m-d H:i:s')) < strtotime(date('Y-m-d H:i:s') . "-1 hour") || empty($ats_group_id)) { 
            // Звонки старше одного часа
            return "https://rec.crmka.pro/" . $time->format('Y/m/d/') . "$id.mp3";
        } else {
            // Недавние звонки
            $ats_group = \App\Models\AtsGroup::find($ats_group_id);
            $ip = $ats_group->ats->ip;
            return "http://$ip/rec/mp3/$id.mp3";
        }        
    }
}