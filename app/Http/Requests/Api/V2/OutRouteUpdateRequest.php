<?php

namespace App\Http\Requests\Api\V2;

class OutRouteUpdateRequest extends OutRouteRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => "required|string|max:80|unique:out_routes,name,$this->out_route",
            'comment' => "max:255",
            'mask' => "required|string|max:100",
            'replace_count' => "integer|max:128",
            'prefix' => "string|max:20",
            'trunks1' => "required|string|max:1000",
            'trunks2' => "sometimes|nullable|string|max:1000",
            'trunks_p1' => "required|integer|max:128",
            'trunks_p2' => "required|integer|max:128",
            'ats_group_id' => "required|integer|exists:ats_groups,id",
            'provider_id' => "required|integer|exists:providers,id",
            'is_work' => "boolean"
        ];
    }
}
