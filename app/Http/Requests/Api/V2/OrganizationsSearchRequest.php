<?php

namespace App\Http\Requests\Api\V2;


class OrganizationsSearchRequest extends Request
{
    public function rules()
    {
        $rules  = [
            'filters.parent_id'  => 'integer|min:1'
//            'page'              => 'integer|min:1',
//            'per_page'          => 'integer|min:1|max:200',
//            'sort_key'          => 'max:100',
//            'sort_direction'    => 'in:asc,desc',
//            'query'             => 'max:500'
        ];

        return $rules;
    }
}
