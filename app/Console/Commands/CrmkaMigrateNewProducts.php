<?php
namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use DB;
use App\Models\Organization;
use App\Models\Project;
use App\Models\ProjectGoal;
use App\Models\ProjectGoalScript;
use App\Models\Status;
use App\Models\User;
use App\Models\Order;
use App\Models\Site;
use App\Models\ProjectPage;
use App\Models\Call;
use App\Models\CallStatus;
use App\Models\PhoneCodes;
use App\Models\Sale;
use App\Models\Model;
use App\Models\OrderImportId;
use App\Models\DeliveryType;
use App\Models\LnkGeoProduct;
use App\Models\Comment;
use Hash;
use Carbon\Carbon;
use App\Exceptions\MigrateException;
use App\Services\UsersService;


class CrmkaMigrateNewProducts extends Command
{
    protected $signature = 'crmka:migrate_new_products:db {--dev} {--limit=1000} {--db=refresh}';

    protected $description = 'migrate new products from old mysql db';

//    protected $devOption = false;

    protected $limit;

    protected $dev;

    protected $project_info;
    protected $project_page_info;
    protected $project_goal_info;

    public function __construct(UsersService $userService)
    {
        parent::__construct();

        $this->userService = $userService;
    }

    public function handle()
    {
        $this->db        = $this->option('db');        
        $this->dev       = (bool)$this->option('dev');
        $this->limit     = (int)$this->option('limit');        

        if($this->db=='refresh'){
            $this->addGeoToProducts();
        }elseif($this->db=='scripts'){
            $this->migrateScripts();
        }
        elseif($this->db=='full'){
            if($this->db=='full'){            
                Model::unguard(true);                        
                $this->migrateProducts();
            }else{
                $database = 'migrate'.ucfirst($this->db);
                try{
                    $this->$database();
                }catch (\Exception $e){
                    echo $e->getMessage()."\n";
                    exit;
                }
            }
            Model::unguard(false);             
        }

        if($this->db!='scripts'){
            $this->call('search:reindex', [
                'index' => 'products'
            ]);               
        }
       
    }    

    function get_crm_geo_id_by_code($geo_id) {
        $geos = array(
            "ru" => 180, //RU
            "by" => 34,  //BY
            "kz" => 116, //KZ
            "ua" => 218, //UA
            "kg" => 107, //KG
            "az" => 17,  //AZ
            "lv" => 127,  //LV
            "am" => 8,  //AM
            "ww" => 238,  //AM
            "uz" => 222, //UZ
            "it" => 102, //IT
            "es" => 62, //ES
            "de" => 52, //DE
            "ng" => 156 //NG
        );
        return $geos[$geo_id];
    }    

    protected function addGeoToProducts()
    {
        $products = Product::all();               
        foreach ($products as $product) {                       
            
            $geos = [];
            if($product->organization_id==2){
                $geos = [180,8,34];
            }            
            if(!empty($geos)){
                var_dump($product->id);
                /*foreach ($geos as $geo) {
                    $temp = LnkGeoProduct::create(['geo_id' => $geo,'product_id'=>$product->id]);
                }*/
                $product->geo()->syncWithoutDetaching($geos);                            
            }
        }        
    }

     protected function migrateScripts()
    {
        $this->import('crm_scripts', 'project_goal_scripts', function ($d) {                                          
            $import_id_arr = explode("_",$d->import_id);                           
            //var_dump($import_id_arr);
            //var_dump( 'reklpro_id_'.$import_id_arr[3]);            
                $project = Project::where('import_id', 'reklpro_id_'.$import_id_arr[3])->get();
                if($project->count()){ 
                    echo PHP_EOL;    
                    var_dump('find project');           echo PHP_EOL;
                    $project_n = $project->first();                
                    $project_goals = $project_n->project_goals()->get();
                    foreach ($project_goals as $project_goal) {
                        if($project_goal->geo_id == $this->get_crm_geo_id_by_code($import_id_arr[1])){
                            $links = [];
                            $links_names = [];
                            $project_goal_scripts = ProjectGoalScript::where('project_goal_id',$project_goal->id)->get();                            
                            foreach ($project_goal_scripts as $project_goal_script) {
                                $links[$project_goal_script->id] = $project_goal_script->link;                                
                                $links_names[$project_goal_script->id] = $project_goal_script->name;                                
                            }
                            var_dump($d->link);echo PHP_EOL;
                            var_dump($links);echo PHP_EOL;
                            
                            if(count($links)==0){                                    
                                echo 'create'.PHP_EOL;
                                $project_goal_script    = ProjectGoalScript::create([                                
                                    'project_goal_id'        => $project_goal->id,
                                    'name'                   => $d->name,
                                    'link'                   => 'scripts/old/'.$d->project_id.'/script/'.$d->link,
                                    'status'                 => ($d->status=='1')?true:false,
                                    'views'                  => 1
                                ]);   
                            }else{                                
                                if(count($links)==1){
                                    foreach ($links as $script_id => $script_link) {
                                        if(stripos($script_link, 'scripts/old/') !== false){
                                            echo 'update'.PHP_EOL;
                                            DB::table('project_goal_scripts')
                                            ->where('id', $script_id)
                                            ->update([
                                            'name'                   => $d->name,
                                            'status'                 => ($d->status=='1')?true:false,
                                            'link' => 'scripts/old/'.$d->project_id.'/script/'.$d->link,
                                            'views'                  => 1
                                            ]);        
                                        }
                                    }                    
                                }else{
                                    foreach ($links as $script_id => $script_link) {
                                        if(stripos($script_link, 'scripts/old/') !== false && $d->name==$links[$script_id]){
                                            echo 'update_2'.PHP_EOL;
                                            DB::table('project_goal_scripts')
                                            ->where('id', $script_id)
                                            ->update([
                                            'name'                   => $d->name,
                                            'status'                 => ($d->status=='1')?true:false,
                                            'link' => 'scripts/old/'.$d->project_id.'/script/'.$d->link,
                                            'views'                  => 1
                                            ]);        
                                        }
                                    }                    
                                }                
                            }
                            //var_dump($project_goal_script);
                            
                        }
                    }
                }            
        });
    }

    protected function migrateProducts()
    {
        $this->import('crm_products', 'products', function ($data) {
            $geos = [];
            if($data->id_organization==66){
                $geos = [116,222];
                //$organization_id = 66;
            }elseif($data->id_organization==101){
                $geos = [107];
                //$organization_id = 101;
            }
            $checks = Product::where('article', $data->article)->get();

            if($checks->count()){                
                //var_dump($geos);
                //var_dump('attach geo');                
                foreach ($checks as $check) {
                    $check->geo()->syncWithoutDetaching($geos);
                }                
            }else{
                var_dump($geos);
                $new_product = Product::create([
                    //'id'                    => $data->id,
                    'code_product'          => $data->code_product,
                    'uniqued_import_id'     => $data->uniqued_import_id,
                    'organization_id'       => 2,                
                    'article'               => $data->article,
                    'img'                   => '[]',                
                    'name'                  => $data->name,
                    'price_cost'            => $data->price_cost,
                    'price_online'          => $data->price_online,
                    'price_prime'           => $data->price_prime,
                    'weight'                => $data->weight,
                    'desc'                  => $data->desc,
                    //'script'                => $data->script,
                    'basic_unit'            => $data->basic_unit,
                    'nabor'                 => $data->nabor,
                    'service'               => $data->service,
                    'complect'              => $data->complect,
                    'basic_unit_seat'       => $data->basic_unit_seat,
                    'is_work'               => $data->is_work=='1' ? true : false
                ]);
                //var_dump($new_product);
                if($new_product){
                  $new_product->geo()->syncWithoutDetaching($geos);  
                }
            }          

        });
    }    

    protected function import($tableFrom, $tableTo, $callback, $updateSec = true)
    {
        $total      = DB::connection('crmka_old')->table($tableFrom)->count();
        if($this->dev && $total > $this->limit) {
            $total  = $this->limit;
        }

        $message    = 'import ' . $tableTo;
        $bar        = $this->bar($total, $message);

        $bar->start();

        try{            
            if($tableFrom=='crm_products'){
                DB::connection('crmka_old')
                ->table($tableFrom)
                ->whereIn('id_organization', [66,101])
                ->orderBy('id', 'desc')
                ->chunk($this->limit, function ($data) use ($bar, $callback){
                    foreach ($data as $d) {
                        $callback($d);
                        $bar->advance();
                    }

                    if($this->dev) {
                        throw new MigrateException('break on' . $this->limit);
                    }
                });            
            }elseif($tableFrom=='crm_scripts'){
                DB::connection('crmka_old')
                ->table($tableFrom)
                ->join('crm_projects', 'crm_projects.id', '=', $tableFrom.'.project_id')
                ->select($tableFrom.'.*', 'crm_projects.import_id')
                ->where('crm_projects.import_id', 'like', 'reklpro_%')
                ->where($tableFrom.'.update_date', '>', 1559077201)
                ->orderBy('id', 'desc')
                ->chunk($this->limit, function ($data) use ($bar, $callback){
                    foreach ($data as $d) {
                        $callback($d);
                        $bar->advance();
                    }

                    if($this->dev) {
                        throw new MigrateException('break on' . $this->limit);
                    }
                });    
            }
        }catch (MigrateException $e){
            //$e->getMessage();
        }

        if($updateSec) {
            $max    = DB::table($tableTo)->max('id') + 1;
            DB::statement('ALTER SEQUENCE ' . $tableTo . '_id_seq RESTART WITH ' . $max . ';');
        }

        $bar->finish();
        $this->line('');
    }

    protected function bar($total, $message = '')
    {
        $bar    = $this->output->createProgressBar($total);

        $bar->setMessage($message);
        $bar->setFormat('%message%  %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s%');

        return $bar;
    }

    protected function isLimit()
    {
        return ($this->option('limit') > 0)  ? $this->option('limit') : 1000;
    }
}
