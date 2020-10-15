<?php
namespace App\Http\Requests\Api\V2;


class ProductUpdateRequest extends Request
{
    public function rules()
    {
        return [
            'name'          => 'required|max:180',
            'article'       => 'required|max:100',
            'desc'          => 'sometimes|nullable',
            'weight'        => 'sometimes|nullable|integer',
            'price_cost'    => 'required|integer',
            'is_work'       => 'nullable',
            'geo_ids'       => 'array',
            'organization_id'   => 'required|integer',
            'category_id'   => 'nullable',
            'is_kit'        =>  'nullable',
            'related_goods' => 'nullable',
            'product_images' => 'array|max:10',
            'product_images.*.product_id' => 'nullable|integer',
            'product_images.*.is_main'    => 'nullable|boolean',
            'product_images.*.image_upload'  => 'nullable|file|mimes:jpeg,png,jpg,gif,svg'
        ];
    }
}