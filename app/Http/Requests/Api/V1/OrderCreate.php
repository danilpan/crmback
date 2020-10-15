<?php
namespace App\Http\Requests\Api\V1;


class OrderCreate extends Request
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id'                    => 'required_without:import_id|max:10|unique:orders,id',
            'import_id'             => 'required_without:id|max:50|unique:orders,import_id',


            'phone'                 => 'required|max:15',
            'site_product_name'     => 'max:255',
            'id_webmaster'          => 'integer|min:1',
            'id_webmaster_transit'  => 'max:255',
            'site_product_price'    => 'max:15',
            'dop_info'              => 'max:5000',
            'id_transit'            => 'max:15',
            
            'id_gasket'             => 'array',
            'id_gasket.id'          => 'integer|max:15',
            'id_gasket.name'        => 'max:100',
            
            'country_code'          => 'max:20',
            'webmaster_type'        => 'max:10',
            'age_id'                => 'nullable',
            
            'project'                   => 'array',
            'project.id'                => 'required_without:project.import_id|max:100',
            'project.import_id'         => 'required_without:project.id|max:100',
            'project.name'              => 'max:100',
            'project.name_for_client'   => 'max:100',
            'project.sms_sender'        => 'max:100',
            'project.countries'         => 'max:100',
            'project.hold'              => 'max:100',
            'project.sex'               => 'integer|max:1',
            'project.desc'              => 'max:255',


//            'id_flow'   => 'array',
//            'id_flow.id'   => 'required|integer|max:15',
//            'id_flow.name'   => 'max:255',
            'profit'            => 'max:10',
            'real_profit'       => 'max:10',
            'request_hash'      => 'max:100',

            'site'                      => 'array',
            'site.import_id'            => 'required_without:site.id|max:100',
            'site.id'                   => 'required_without:site.import_id|max:100',
            'site.name'                 => 'max:100',
            'site.import_project_id'    => 'max:100',
            'site.url'                  => 'max:100',
        ];
    }
}