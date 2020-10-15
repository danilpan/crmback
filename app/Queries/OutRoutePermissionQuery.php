<?php
namespace App\Queries;

use Illuminate\Support\Facades\DB;

/**
 * 
 */
class OutRoutePermissionQuery extends PermissionQuery
{
    protected $table = "out_routes";
    
    /**
     * Возвращает коллекцию исходящих маршрутов, доступных пользователю
     * Доступность проверяется в следующем порядке:
     *      Организации по роли 
     *      Дочерние организации по доступам
     *      Группы АТС по организациями
     *      Маршруты по группам
     * @method getAllAccessOutRoutes
     * @param  integer $organization_id ID организации пользователя
     * @param  boolean $with_children   Доступны ли маршруты групп дочерних организаций
     * @return Collection
     */
    public function getAllAccessOutRoutes($organization_id, $with_children, $filter=[])
    {
        // Получаем ID доступных по роли организаций 
        // с дочерними, если есть доступ
        $orgs = $this->getAllAccessCompanyIDs($organization_id, $with_children);

        $orgJoin = function ($join) use ($orgs) {
            $join->on('o_a.organization_id', '=', 'o.id')
                    ->whereIn('o.id', $orgs);
            };
    
        $where = [];
        foreach ($filter as $key => $value) {
            switch ($key) {
                case 'is_work':
                    if ($value == 'true') {
                        $where[] = ["r.$key", true];
                    }
                    break;
                    
                default:
                    $where[] = ["r.$key", $value];
                    break;
            }
        }
        $out_routes = DB::table('ats_groups')->select('ats_groups.id')
            ->join('lnk_ats_group__organization as o_a', 'o_a.ats_group_id', '=', 'ats_groups.id')->select('o_a.organization_id')
            ->join('organizations as o', $orgJoin)->select('o.id')
            ->join('out_routes as r', 'r.ats_group_id', '=', 'ats_groups.id')
            ->select('r.*', 'o.id as organization_id')->where($where)->get();
        
        $uniq = $out_routes->unique('id');
        return $uniq->values()->all();
    }
}
