<?php

namespace App\Models;

class Project extends Model
{
    protected $fillable = [
		'id',
		'title',
		'description',
		'organization_id',
		'name_for_client',
        'name_en',
		'sms_sender',
		'hold',
		'url',
		'is_private',
		'is_call_tracking',
		'is_authors',  
		'is_resale',
        'is_postcode_info',
		'category_id',
		'image',
		'gender',
		'postclick',
        'age',
        'project_category_kc_id',
        'replica',
        'operator_notes'
	];

    protected $casts = [
	    'age' => 'array'
    ];

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_project');
    }

    public function category()
    {
        return $this->belongsTo(ProjectCategory::class);
    }

    public function project_page()
    {
        return $this->hasMany(ProjectPage::class);
    }

    public function geo()
    {
        return $this->belongsToMany(
            Geo::class,
            'projects_geos',
            'project_id',
            'geo_id'
        )->withTimestamps();
    }

    public function traffics()
    {
        return $this->belongsToMany(
            Traffic::class,
            'project_traffic',
            'project_id',
            'traffic_id'
        )->withTimestamps();
    }

    public function products()
    {
        return $this->belongsToMany(
            Product::class,
            'product_project',
            'project_id',
            'product_id'
        )->withTimestamps();
    }

    public function related_products()
    {
        return $this->belongsToMany(
            Product::class,
            'related_products',
            'project_id',
            'product_id'
        )->withPivot('count')->orderBy('count','DESC');
    }

    public function delivery_types()
    {
        return $this->belongsToMany(
            DeliveryType::class,
            'delivery_type_project',
            'project_id',
            'delivery_type_id'
        )->withTimestamps()->withPivot('geo_id');
    }


    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function project_category_kc()
    {
        return $this->belongsTo(ProjectCategoryKc::class);
    }

    public function project_script()
    {
        return $this->hasMany(ProjectScript::class);
    }

    public function project_goals(){
        return $this->hasMany(ProjectGoal::class,'project_id');
    }
}
