<?php
namespace App\Models;

class Product extends Model
{
    protected $fillable = [
        "name",
	    "article",
	    "desc",
	    "weight",
	    "price_cost",
        "organization_id",
        "is_work",
        "category_id",
        'is_kit',
        'price_online',
        'price_prime',
        'related_goods',
        'code_product',
        'uniqued_import_id'
    ];

    protected $casts = [
        'related_goods' => 'array'
        ];

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function projects()
    {
        return $this->belongsToMany(
            Project::class,
            'product_project',
            'product_id',
            'project_id'
        );
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function geo(){
        return $this->belongsToMany(
            Geo::class,
            'lnk_geo_product',
            'product_id',
            'geo_id');
    }
}
