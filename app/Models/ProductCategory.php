<?php

namespace App\Models;

class ProductCategory extends Model
{
    protected $fillable = [
        'name',
        'organization_id',
        'is_work'
    ];

    protected $primaryKey = 'id';

    protected $table = 'product_category';

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function products()
    {
        return $this->belongsToMany(
            Product::class,
            'products_categories',
            'category_id',
            'product_id'
        );
    }
}
