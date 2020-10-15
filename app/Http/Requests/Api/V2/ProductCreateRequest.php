<?php
namespace App\Http\Requests\Api\V2;


class ProductCreateRequest extends Request
{
    public function rules()
    {
        return [
            'name'          => 'required|max:180',
            'article'       => 'required|max:100',
            'desc'          => 'nullable',
            'weight'        => 'nullable|integer',
            'price_cost'    => 'required|integer',
            'is_work'       => 'nullable',
            'organization_id'   => 'required|integer',
            'category_id'   => 'nullable|integer',
            'is_kit'        => 'nullable',
            'related_goods' => 'nullable',
            'geo_ids'       => 'array',
            'product_images'                => 'array|max:10',
            'product_images.*.product_id'   => 'nullable|integer',
            'product_images.*.is_main'      => 'nullable|boolean',
            'product_images.*.image_upload' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg'
        ];
    }
}