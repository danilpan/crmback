<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\AtsQueue;


class Organization extends Node
{
    protected $fillable = [
        'title',
        'permission_id',
        'role_id',
        'is_company',
        'api_key',
        'parent_id'
    ];

    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }

    public function product_category()
    {
        return $this->hasMany(ProductCategory::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'lnk_organizations_roles',
			'organization_id',
            'role_id'
        )->withTimestamps();
	}
        
    public function ats_groups()
    {
        return $this->belongsToMany('App\Models\AtsGroup', 'lnk_ats_group__organization');
    }
    
    /**
     * Очереди созданные компанией
     * @method ats_queues
     * @return Relation
     */
    public function ats_queues()
    {
        return $this->hasMany(AtsQueue::class);
    }
    
    /**
     * Очереди привязанные к компании по роли
     * @method ats_queues_binded
     * @return Relation
     */
    public function ats_queues_binded()
    {
        return $this->belongsToMany(AtsQueue::class, 'lnk_ats_queue__organization');
    }

    /**
     * ID всех дочерних организаций
     * @method getChildIds
     * @return array
     */
    public function getChildIds()
    {
        $ids = [$this->id];

        foreach ($this->children as $child){
            $ids = array_merge($ids, $child->getChildIds());
        }

        return $ids;
    }

    /**
     * Пользователи, закрепленные за организацией
     * @method users
     * @return HasMany
     */
    public function users()
    {
        return $this->hasMany(User::class, 'organization_id');
    }
}
