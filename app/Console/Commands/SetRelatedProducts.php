<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use DB;

class SetRelatedProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SetRelatedProducts:set';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Установка продаваемых товаров по проектам';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        
        $sales = DB::table('sales')
            ->join('order_project', 'sales.order_id', '=', 'order_project.order_id')
            ->select('sales.product_id', 'order_project.project_id', DB::raw("count(sales.id) as count"))          
            ->groupBy('order_project.project_id', 'sales.product_id')  
            ->get()->toArray();
        
        DB::table('related_products')->delete();       
        
        $sales_arr = [];
        foreach ($sales as $key => $value) {
            $sales_arr[] = (array)$value;
        }
        $collection = collect($sales_arr);   //turn data into collection

        $chunks = $collection->chunk(2000); //chunk into smaller pieces
        $chunks->toArray(); //convert chunk to array        
        
        foreach($chunks as $chunk)
        {
            DB::table('related_products')->insert($chunk->toArray());                   
        }

    }
}
