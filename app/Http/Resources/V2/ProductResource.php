<?php
namespace App\Http\Resources\V2;

class ProductResource extends Resource
{
    public function toArray($request)
    {
        $data   = [
            'id'    => $this->id,
            'name'  => $this->name,
            'code_product' => $this->code_product,
            'uniqued_import_id' => $this->uniqued_import_id,
            'organization_id' => ["value"=>$this->organization['id'],"text"=>$this->organization['title']],
            'organizations' => $this->organization->only(['id', 'title','parent_id', 'is_company']),
            'cat_id' => $this->cat_id,
            'article' => $this->article,
            //'category_id' => ["value"=>$this->category['id'],"text"=>$this->category['name']],
            'product_category' => $this->category,
            'img' => $this->img,
            'parent_project' => $this->parent_project,
            'parent_site' => $this->parent_site,
            'price_cost' => $this->price_cost,
            'price_online' => $this->price_online,
            'price_prime' => $this->price_prime,
            'weight' => $this->weight,
            'desc' => $this->desc,
            'script' => $this->script,
            'basic_unit' => $this->basic_unit,
            'nabor' => $this->nabor,
            'service' => $this->service,
            'complect' => $this->complect,
            'basic_unit_seat' => $this->basic_unit_seat,
            'is_work' => $this->is_work,
            'is_work_title' => $this->is_work_title,
            'age' => $this->age,
            'key' => $this->key,
            'show' => false,
            'is_kit' => $this->is_kit,
            'related_goods' => $this->related_goods,
            'product_images' => $this->images()->orderBy('is_main', 'desc')->get(),
            'geo' => $this->geo
        ];

        return $data;
    }
}
