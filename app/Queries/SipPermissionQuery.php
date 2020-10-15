<?php
namespace App\Queries;

use Illuminate\Support\Facades\DB;

/**
 * 
 */
class SipPermissionQuery extends PermissionQuery
{
    /**
     * Возвращает коллекцию caller ID транков, доступных пользователю
     * 
     * @method getAllAccessOutRoutes
     * @param  integer $organization_id ID организации пользователя
     * @param  boolean $with_children   Доступны ли маршруты групп дочерних организаций
     * @return Collection
     */
    public function getAllAccessCallerIDs($organization_id, $with_children, $filter=[])
    {
        // Получаем ID доступных по роли организаций 
        // с дочерними, если есть доступ
        $orgs = $this->getAllAccessCompanyIDs($organization_id, $with_children);
        
        $by_sip = DB::table('lnk_ats_group__organization as o_a')
            ->whereIn('o_a.organization_id', $orgs)
            ->join('ats_groups as ag', 'ag.id', 'o_a.ats_group_id')
            ->join('sips as s', function($join) {
                $join->on('s.ats_group_id', '=', 'ag.id')
                    ->where('s.is_work', true);
            })
            ->join('sip_caller_ids as cid', 'cid.sip_id', '=', 's.id')
            ->select('cid.*');
            
        $items = DB::table('lnk_ats_group__organization as o_a')
            ->whereIn('o_a.organization_id', $orgs)
            ->join('ats_groups as ag', 'ag.id', 'o_a.ats_group_id')
            ->join('ats_users as au', function($join) {
                $join->on('au.ats_group_id', '=', 'ag.id')
                    ->where('au.type', 'independent');
            })
            ->join('sip_caller_ids as cid', 'cid.ats_user_id', '=', 'au.id')
            ->select('cid.*')
            ->union($by_sip)->groupBy('cid.id')->get();
        
        return $items;
    }
}
