<?php
namespace App\Http\Resources\V1;

class Order extends Resource
{
    public function toArray($request)
    {
        $data   = [
            'id'                    => $this->id,
            'import_id'             => $this->import_id,
            'phone'                 => $this->phone,
            'site_product_name'     => $this->site_product_name,
            'id_webmaster'          => $this->webmaster_id,
            'id_webmaster_transit'  => $this->webmaster_transit_id,
            'site_product_price'    => $this->site_product_price,
            'dop_info'              => $this->description,
            'id_transit'            => $this->transit_id,
            'country_code'          => $this->country_code,
            'webmaster_type'        => $this->webmaster_type,
            'profit'                => $this->profit,
            'real_profit'           => $this->real_profit,
            'type'                  => $this->type
        ];

        if($this->project) {
            $data['project']    = [
                'id'                => $this->project->id,
                'import_id'         => $this->project->import_id,
                'name'              => $this->project->name,
                'name_for_client'   => $this->project->name_for_client,
                'sms_sender'        => $this->project->sms_sender,
                'countries'         => $this->project->countries,
                'hold'              => $this->project->hold,
                'sex'               => $this->project->sex,
                'desc'              => $this->project->desc
            ];
        }

        if($this->site) {
            $data['site']   = [
                'id'                    => $this->site->id,
                'import_id'             => $this->site->import_id,
                'name'                  => $this->site->name,
                'project_id'            => $this->site->project_id,
                'url'                   => $this->site->url
            ];
        }

        if($this->gasket) {
            $data['id_gasket']   = [
                'id'                    => $this->gasket->id,
                'name'                  => $this->gasket->name,
                'url'                   => $this->gasket->url,
                'id_product'            => $this->gasket->id_product,
                'cr'                    => $this->gasket->cr
            ];
        }

        return $data;
    }
}