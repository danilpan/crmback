<?php

namespace App\Http\Resources\V2;


class ProjectGoalScriptResource extends Resource
{
    public function toArray($request)
    {        
        $cross_sales = [];
        $cross_sales_arr = $this->cross_sales->toArray();
        foreach ($cross_sales_arr as $cross_sale) {
            $cross_sales[] = [
                'name'=>$cross_sale['name'],
                'product_id'=>$cross_sale['pivot']['product_id'],
                'note'=>$cross_sale['pivot']['note'],
                'price'=>$cross_sale['pivot']['price'],
                'type'=>(string)$cross_sale['pivot']['type'],
                'uniq'=>uniqid()
            ];
        }
        $data   = [
            'id'    => $this->id,
            'project_goal_id'    => $this->project_goal_id,
            'name' => $this->name,
            'link' => $this->link,
    	    'cross_sales' => $cross_sales,
            'status' => $this->status,
    	    'views' => $this->views
        ];

        return $data;
    }
}
