<?php
namespace App\Queries;

use Illuminate\Support\Facades\DB;

class SipCallerIdPermissionQuery extends PermissionQuery {
    
    public function getForIn($organization_id, $with_children)
    {
        /*
        - Сделать метод по получению каллер айди на фронт, 
        (каллерайди храняться в базе данных sips_caller_ids) для подключения их в очередь, и 
        принятия ими входящих вызовов. Номера телефонов (КаллерАйди) должны привязываться 
        только к очередям с "type" = "in".
        Условия:
        
        +Если каллер айди добавлен уже в одну из очередей, его нельзя привязывать к другой очереди, и не отображать при поиске каллерайди на фронте.
        +Если каллер айди не добавлен не в одну из очередей, то его можно привязать к очереди.
        +Доп. условия добавления:
        +Выдаем каллер айди если (если каллер айди принадлежит транку из таблицы sips):
        +sips_id != null
        +caller_id не привязан к другой очереди.
        +Группа к которой привязан сип sip найденный по sips_id доступна пользователю по настройкам доступов.
        */
        $orgs = $this->getAllAccessCompanyIDs($organization_id, $with_children);
        
        $binded = function($query) {
            $query->select('sip_caller_id_id')->from('lnk_ats_queue__sip_caller_id');
        };

        $by_sip = DB::table('lnk_ats_group__organization as o_a')
            ->whereIn('o_a.organization_id', $orgs)
            ->join('ats_groups as ag', 'ag.id', '=', 'o_a.ats_group_id') // Группы доступные мне
            ->join('sips as s', 's.ats_group_id', '=', 'ag.id') // Транк в доступной группе
            ->join('sip_caller_ids as ci', function ($join) use ($binded) {
                $join->on('ci.sip_id', '=', 's.id')  // КаллерАйди принадлежит транку
                     ->whereNull('ci.ats_queue_id')  // КаллерАйди не привязан к другой очереди (one-to-many)
                     ->whereNotIn('ci.id', $binded); // каллер айди не привязан к очереди      (many-to-many)
            })
            ->select('ci.*');
        
        /*
        +Доп. условия добавления:
        +Выдаем каллер айди если (если каллер айди принадлежит транку из таблицы sips):
        +sips_id != null
        +caller_id не привязан к другой очереди.
        +Группа к которой привязан сип sip найденный по sips_id доступна пользователю по настройкам доступов.
        */
            
        $items = DB::table('lnk_ats_group__organization as o_a')
            ->whereIn('o_a.organization_id', $orgs)
            ->join('ats_groups as ag', 'ag.id', '=', 'o_a.ats_group_id') // Группа доступна мне
            ->join('ats_users as au', function ($j) {
                $j->on('au.ats_group_id', '=', 'ag.id') // АТС юзер в группе
                    ->where('au.type', '=', 'independent'); // тип: независимое использование
            })
            ->join('sip_caller_ids as ci', function ($j) use ($binded) {
                $j->on('ci.ats_user_id', '=', 'au.id') // каллер айди АТС юзера
                    ->whereNull('sip_id') // каллер айди НЕ транка
                    ->whereNull('ci.ats_queue_id') // КаллерАйди не привязан к другой очереди (one-to-many)
                    ->whereNotIn('ci.id', $binded); // каллер айди не привязан к очереди      (many-to-many)  
            })     
            ->select('ci.*')->union($by_sip)->groupBy('ci.id')->get();
    
        return $items;
    }
    
    /**
     * Return Caller IDs of agents
     * 
     * @method getOperators
     * @param string  $type         'in' or 'auto'
     * @param integer $ats_group_id ID of AtsGroup binded to current AtsQueue
     * @param boolean $is_work       Is work time or not
     * @return Collection
     */
    public function getOperators($type, $ats_group_id, $is_work, $organization_id)
    {
        /*
        - Сделать метод по поиску пользователей АТС, для привязки их к очереди.
        Условия:

        1) Если очередь "type" = auto (Автодозвон) и время очереди в данный момент рабочее, 
            что настроено в полях off_time_1 и off_time_2 то оператор может привязываться только к 
            одной из очередей, если он есть в другой очереди, поиск не должен его выдавать, и 
            оператор не должен привязываться в другую очередь автодозвона.
        2) Если очередь "type" = auto (Автодозвон) и время очереди в данный момент НЕ рабочее, 
            что настроено в полях off_time_1 и off_time_2 то оператор может привязываться к 
            нескольким из очередей, если он есть в другой очереди, поиск должен его выдавать, и 
            оператор должен привязываться в другую очередь автодозвона.
        3) Если очередь "type" = in (входящяя) в эту очередь должна быть возможность привязывать 
            любых операторов, из других входящих очередей, а так же очередей автодовзона.
        
        Доп условия:
        4) Поиск делается по таблице sips_caller_ids
        +5) sips_id == null
        +6) Пользователь АТС найденный по ats_users_id имеет пометку type = privat и 
            поле users_id != ''
        +7) Группа очереди должна совподать с группой пользователя АТС.
         */
        
        $orgs = $this->getAllAccessOrganizationIDs($organization_id, true);
            
        if ($type == 'auto' && $is_work) { //1) если тип очереди авто и время рабочее 
            
            $query = DB::table('users as u')
                ->whereIn('u.organization_id', $orgs)
                ->join('organizations as o', 'o.id', '=', 'u.organization_id')
                ->join('ats_users as au', function($join) use ($ats_group_id) {
                    $join->on('au.user_id', '=', 'u.id')
                         ->where('au.ats_group_id', $ats_group_id) //7) агент в той же группе, что и очередь
                         ->where('type', 'privat'); //6) пользователь атс имеет тип приват
                })
                ->join('sip_caller_ids as ci', function($join) {
                    $binded = function($q) {
                        $q->select('sip_caller_id_id')->from('lnk_ats_queue__sip_caller_id');
                    };
                    $join
                        ->on('ci.ats_user_id', '=', 'au.id') // каллер айди найденых пользователей атс
                        ->whereNull('ci.sip_id') //5) каллер айди НЕ транка
                        ->whereNotIn('ci.id', $binded); //1-3) 
                });
        } else {
            $query = DB::table('users as u')
                ->whereIn('u.organization_id', $orgs)
                ->join('organizations as o', 'o.id', '=', 'u.organization_id')
                ->join('ats_users as au', function($join) use ($ats_group_id) {
                    $join->on('au.user_id', '=', 'u.id')
                         ->where('au.ats_group_id', $ats_group_id) //7) агент в той же группе, что и очередь
                         ->where('type', 'privat'); //6) пользователь атс имеет тип приват
                })
                ->join('sip_caller_ids as ci', function($join) {
                    $join
                        ->on('ci.ats_user_id', '=', 'au.id') // каллер айди найденых пользователей атс
                        ->whereNull('ci.sip_id'); //5) каллер айди НЕ транка
                });
        }
            
        $items = $query->select('ci.id', 
                                'ci.caller_id',
                                'u.first_name as first_name', 
                                'u.middle_name as middle_name', 
                                'u.last_name as last_name',
                                'o.title as organization')/*->groupBy('ci.id')*/->orderBy('ci.id')->get();
        
        return $items;
    }
}

























