<?php
namespace App\Http\Requests\Api\V2\Optovichok;

use App\Http\Requests\Api\V2\Request;
use App\Models\Organization;
use Illuminate\Validation\Rule;

class ClientRequest extends Request
{
    public function rules()
    {
        $id     = $this->route()->parameter('id');
        $organization_id = request()->organization_id;
        $companies = Organization::where('is_company', true)->pluck('id');
        $rules  = [
            'client_name'    => 'required|min:2|max:50',
            'phone'         => [
                'max:12|regex:/^(7)[0-9]{10}$/',
                Rule::unique('clients')->ignore($id)->where('organization_id', $organization_id)
            ],
            'iin'           => 'nullable|max:12',
            'type'          => 'required|integer',
            'advert_source_id' => 'required|integer',
            'organization_id'  => [
                'required',
                Rule::in($companies)
            ],
        ];

        return $rules;
    }
}