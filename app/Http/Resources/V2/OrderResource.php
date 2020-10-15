<?php
namespace App\Http\Resources\V2;

use App\Models\User;


class OrderResource extends Resource
{
    
    public function toArray($request)
    {
        if($this->sales instanceof Collection){
            if($this->sales->isNotEmpty()){
                $sales=SaleResource::collection($this->sales);
            }else{
                $projectProducts = $this->project_products()->get()->pluck('products')->flatten();
                if($projectProducts->isNotEmpty()){
                    $sales = OrderProductResource::collection($projectProducts);
                } else{
                    $sales = $this->sales;
                }
            }
        } else {
            $sales = $this->sales;
        }

        $script = "";
        $cross_sales = [];

        if(isset($this->script) && !empty($this->script)){
            $script = $this->script['link'];
            foreach ($this->script['cross_sales'] as $cross_sale) {
                $cross_sales[] = [
                    'id'=>$cross_sale['pivot']['product_id'],
                    'name'=>$cross_sale['name'],
                    'note'=>$cross_sale['pivot']['note'],
                    'price_online'=>$cross_sale['pivot']['price'],
                    'type'=>$cross_sale['pivot']['type'],
                    'article'=>$cross_sale['article']
                ];                
            }
        }

        $dop_info = [
            'import_webmaster_id'           => $this->import_webmaster_id,
            'type_dop_info'                 => $this->type,
            'import_id'                     => $this->import_id
        ];        
        
        $data   = [
            'id'                            => $this->id,
            'key'                           => (is_array($this->key)) ? array_first($this->key) : $this->key,
            'dop_info'                      => $dop_info,
            'call_manager'                  => ($this->manager) ? ["id"=>$this->manager->id,"name"=>$this->manager->first_name." ".$this->manager->last_name] : ["id"=>0,"name"=>"Менеджер не указан"],
            'black_list'                    => $this->black_list,            
            'black_list_user'               => $this->black_list_user,
            //'import_id'                     => $this->import_id,
            //'import_webmaster_id'           => $this->import_webmaster_id,
            'import_webmaster_transit_id'   => $this->import_webmaster_transit_id,
            //'request_hash'                  => $this->request_hash,
            // 'organization_id'               => $this->organization_id,
            'organization_id'               => 10,
            'organizations'                 => $this->organization->only(['id', 'title']),
            //'api_key'                       => $this->api_key,
            'type'                          => $this->type,
            'dial_step'                     => $this->dial_step,
            'dial_steps'                    => $this->dial_steps,
            'delivery_date_finish'          => ($this->delivery_date_finish) ? $this->delivery_date_finish->format('Y-m-d H:i:s') : null,
            'delivery_time_1'               => ($this->delivery_time_1) ? $this->delivery_time_1 : null,
            'delivery_time_2'               => ($this->delivery_time_2) ? $this->delivery_time_2 : null,
            'country_code'                  => $this->country_code,
            //'info'                          => $this->info,
            'phones'                        => $this->phones,
            'client_name'                   => $this->client_name,
            'sex_id'                        => $this->sex_id,
            'sites'                         => SiteResource::collection($this->whenLoaded('sites')),            
            'projects'                      => $this->projects->first(),
            'project_category_kc'           => ($this->project_category_kc) ? $this->project_category_kc[0] : null,

            'projects_name'                 => ($this->projects) ? ProjectResource::collection($this->whenLoaded('projects'))->implode('title', '|') : null,
            'dial_time'                     => ($this->dial_time) ? $this->dial_time->format('Y-m-d H:i:s') : null,
            'date_status_1'                 => ($this->date_status_1) ? $this->date_status_1->format('Y-m-d H:i:s') : null,
            'date_status_2'                 => ($this->date_status_2) ? $this->date_status_2->format('Y-m-d H:i:s') : null,
            'date_status_3'                 => ($this->date_status_3) ? $this->date_status_3->format('Y-m-d H:i:s') : null,
            'date_status_4'                 => ($this->date_status_4) ? $this->date_status_4->format('Y-m-d H:i:s') : null,
            'date_status_5'                 => ($this->date_status_5) ? $this->date_status_5->format('Y-m-d H:i:s') : null,
            'created_at'                    => ($this->created_at) ? $this->created_at->format('Y-m-d H:i:s') : null,
            'ordered_at'                    => ($this->ordered_at) ? $this->ordered_at->format('Y-m-d H:i:s') : null,
            'arrival_office_date'           => ($this->arrival_office_date) ? $this->arrival_office_date->format('Y-m-d H:i:s') : null,
            
            'delivery_time'                 => ($this->delivery_time) ? $this->delivery_time->format('Y-m-d H:i:s') : null,
            //'full_address'                  => ['addressRus' => $this->full_address],
            'region'                        => $this->region,
            'area'                          => $this->area,
            'city'                          => $this->city,
            'street'                        => $this->street,
            'home'                          => $this->home,
            'housing'                       => $this->housing,
            'room'                          => $this->room,
            'postcode'                      => $this->postcode,                
            'statuses'                      => $this->statuses,
            'status_1'                      => $this->status_1,
            'status_2'                      => $this->status_2,
            'status_3'                      => $this->status_3,
            'status_4'                      => $this->status_4,
            'status_5'                      => $this->status_5,
            'status_6'                      => $this->status_6,
            'status_7'                      => $this->status_7,
            'status_8'                      => $this->status_8,
            'status_9'                      => $this->status_9,
            'status_10'                     => $this->status_10,
            'project_pages'                 => $this->project_pages,
            'project_page'                  => ($this->project_page) ? $this->project_page : null,
            'script'                        => $script,
            'cross_sales'                   => $cross_sales,
            'operator'                      => $this->operator,
            'delivery_type'                 => $this->delivery_types,
            'delivery_types_id'             => $this->delivery_types_id,
            'delivery_types_price'          => $this->delivery_types_price,                        
            'surplus_percent_price'          => $this->surplus_percent_price,
            'comments'                      => CommentResource::collection($this->comments),
            // 'comments'                      => $this->comments,
            'sales'                         => $this->sales,
            'sales_order'                   => $sales,
            'warehouse_data'                => ['Ref' => $this->warehouse_id,
                                                'Description' => $this->warehouse],
            'history'                       => ($this->history_с) ? $this->history_с : [],
            'calls_t'                       => ($this->calls) ? CallResource::collection($this->calls) : [],
            //'history'                     => $this->history,
            'related_products'              => RelatedProductResource::collection($this->related_products()->get()->pluck('related_products')->flatten()),
            'items'                         => $this->items,   
            'geo'                           => ($this->geo) ? $this->geo : ['name_ru'=>'Неизвестно','name_en'=>'Unknown'],
            'summary'                       => $this->summary,
            'current_1_group_status_id'     => $this->current_1_group_status_id,
            'status_group_5'                => $this->status_group_5,
            'status_group_9'                => $this->status_group_9,
            'status_group_17'               => $this->status_group_17,
            'status_group_17_percent'       => $this->status_group_17_percent,
            'status_group_18'               => $this->status_group_18,
            'status_group_18_percent'       => $this->status_group_18_percent,
            'status_group_19'               => $this->status_group_19,
            'status_group_19_percent'       => $this->status_group_19_percent,
            'status_group_58'               => $this->status_group_58,
            'status_group_58_percent'       => $this->status_group_58_percent,
            'expected_without_trash'        => $this->expected_without_trash,
            'approve_common'                => $this->approve_common,
            'approve_clear'                 => $this->approve_clear,
            'approve_processed'             => $this->approve_processed,
            'approved'                      => $this->approved,
            'rejected'                      => $this->rejected,

            'status_approved'               => $this->status_approved,

            //salary_report
/*            'C1'                            => $this->C1,
            'C2'                            => $this->C2,
            'C3'                            => $this->C3,
            'UP1'                           => $this->UP1,
            'UP2'                           => $this->UP2,
            'UP3'                           => $this->UP3,
            'T1'                            => $this->T1,
            'T2'                            => $this->T2,
            'C1_quantity'                   => $this->C1_quantity,
            'RC1'                           => $this->RC1,
            'C2_quantity'                   => $this->C2_quantity,
            'RC2'                           => $this->RC2,
            'C3_quantity'                   => $this->C3_quantity,
            'RC3'                           => $this->RC3,
            'UP1_quantity'                  => $this->UP1_quantity,
            'RUP1'                          => $this->RUP1,
            'UP2_quantity'                  => $this->UP2_quantity,
            'RUP2'                          => $this->RUP2,
            'UP3_quantity'                  => $this->UP3_quantity,
            'RUP3'                          => $this->RUP3,
            'T1_quantity'                   => $this->T1_quantity,
            'RT1'                           => $this->RT1,
            'T2_quantity'                   => $this->T2_quantity,
            'RT2'                           => $this->RT2,
            'confirmed_sales'               => $this->confirmed_sales,
            'confirmed_upsales'             => $this->confirmed_upsales,
            'free_time'                     => $this->free_time,
            'number'                        => $this->number,
            'workload_percent'              => $this->workload_percent,
            'total_sum'                     => $this->total_sum,*/


            'upsale_1'                      => $this->upsale_1,
            'upsale_2'                      => $this->upsale_2,
            'upsale_sum'                    => $this->upsale_sum,
            'upsale_sum_percent'            => $this->upsale_sum_percent,
            'price_avg'                     => $this->price_avg,
            'count_clear'                   => $this->count_clear,
            'count_clear_percent'           => $this->count_clear_percent,
            'count_common'                  => $this->count_common,
            'full_address'                  => $this->full_address,
            'delivery_price'                => $this->delivery_price,
            'products_total'                => $this->products_total,
            'status_1c_1'                   => $this->status_1c_1,
            'status_1c_2'                   => $this->status_1c_2,
            'status_1c_3'                   => $this->status_1c_3,
            'status_1c_3_time'              => ($this->status_1c_3_time) ? $this->status_1c_3_time->format('Y-m-d H:i:s') : null,
            'request_hash'                  => $this->request_hash,
            'barcode'                       => $this->barcode,
            'responsible_id'                => $this->responsible_id,
            'gasket_id'                     => $this->gasket_id,
            'webmaster_id'                  => $this->webmaster_id,
            'flow_id'                       => $this->flow_id,
            'client_email'                  => $this->client_email,
            'phone_country'                 => $this->phone_country,
            'referer'                       => $this->referer,
            'second_id'                     => $this->second_id,
            'webmaster_transit_id'          => $this->webmaster_transit_id,
            'webmaster_type'                => $this->webmaster_type,
            'time_zone'                     => $this->time_zone,
            'cost_main'                     => $this->cost_main,
            'top_t'                         => $this->top_t,
            'device_id'                     => $this->device_id,
            'source_id'                     => $this->source_id,
            'age_id'                        => $this->age_id,
            'comment_client'                => $this->comment_client,
            'is_unload'                     => $this->is_unload,
            'key_lead'                      => $this->key_lead,
            'track_number'                  => $this->track_number,
            'manager_id'                    => $this->manager_id,
            'project_goal_id'               => $this->project_goal_id,
            'profit'                        => $this->profit,                
            'real_profit'                   => $this->real_profit,
            'project_goal_script_id'        => $this->project_goal_script_id,
            'is_double'                     => $this->is_double,
            'order_sender_id'               => $this->order_sender_id

        ];                   

//            'id'                            => $model->id,
//            'key'                           => $model->key,
//            'import_id'                     => $model->import_id,
//            'import_webmaster_id'           => $model->import_webmaster_id,
//            'import_webmaster_transit_id'   => $model->import_webmaster_transit_id,
//            'request_hash'                  => $model->request_hash,
//            'api_key'                       => $model->api_key,
//            'type'                          => $model->type,
//            'country_code'                  => $model->country_code,
//            'info'                          => $model->info

        return $data;
    }
}
