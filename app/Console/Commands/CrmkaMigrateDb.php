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
use App\Models\Comment;
use Hash;
use Carbon\Carbon;
use App\Exceptions\MigrateException;
use App\Services\UsersService;


class CrmkaMigrateDb extends Command
{
    protected $signature = 'crmka:migrate:db {--dev} {--limit=1000} {--db=full} {--org_seed=no}';

    protected $description = 'migrate data from old mysql db';

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
        $this->org_seed = $this->option('org_seed');
        $this->dev       = (bool)$this->option('dev');
        $this->limit     = (int)$this->option('limit');
        $this->project_info = [];
        $this->project_page_info = [];
        $this->project_goal_info = [];

        if($this->db=='full'){

            $this->call('migrate:fresh');
            Model::unguard(true);

            //$this->call('db:seed',['--class'=>'GeosSeeder']);

            if($this->org_seed == 'no')$this->migrateOrganizations();
            $this->migrateProjects();
            if($this->org_seed == 'no')$this->migrateSales();
            $this->migrateStatuses();
            if($this->org_seed == 'no')$this->migrateComments();
            $this->migrateProjectPages();
            if($this->org_seed == 'no')$this->migrateDeliveryTypes();
            //$this->migrateSites();
            if($this->org_seed == 'no')$this->migrateUsers();
            if($this->org_seed == 'no')$this->migrateOrderImportIds();
            $this->migrateReklProGoals();
            $this->migrateScripts();
            if($this->org_seed == 'no')$this->migrateOrders();            
            if($this->org_seed == 'no')$this->migrateCalls();            
            if($this->org_seed == 'no')$this->migrateCallStatuses();
            $this->migrateProducts();
            $this->migratePhoneCodes();   



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
        $this->call('db:seed');
        $this->addGeoToProject();        
        $this->call('search:create_index');
        $this->call('search:reindex');
        
       // $this->userService->updateAllUserCompany();
    }

    protected function migrateOrganizations()
    {
        // Test database connection
        try {
            echo "Old db connection test";
            DB::connection('crmka_old')->getPdo();
        } catch (\Exception $e) {
            die("Could not connect to the database.  Please check your configuration. error:" . $e );
        }

        $old            = DB::connection('crmka_old')->table('crm_roles_list')->get();
        $organization   = Organization::create([
            'id'    => 1,
            'title' => 'CRMKA.pro'
        ]);

        $bar    = $this->bar($old->count(), 'import organizations');
        $bar->start();


        $makeTree   = function($root, $data) use (&$makeTree, $bar){
            foreach($data as $org) {
                if($org->parent_id == $root->id) {
                    $organization   = Organization::create([
                        'id'    => $org->id,
                        'title' => $org->name
                    ]);

                    $organization->makeChildOf($root);
                    $bar->advance();

                    $makeTree($organization, $data);
                }
            }
        };

        $makeTree($organization, $old);

        $max    = DB::table('organizations')->max('id') + 1;
        DB::statement('ALTER SEQUENCE organizations_id_seq RESTART WITH ' . $max . ';');

        $bar->finish();
        $this->line('');
    }

    protected function migrateReklProGoals()
    {
        $this->import('goals', 'project_goal', function ($d) {        

            //$project_import_id = 'reklpro_'.($this->get_geo_code($d->geo_id)).'_id_'.$d->offer_id;

            $project = Project::where('import_id','reklpro_id_'.$d->offer_id)->first();

            if($project){
                $project_goal    = ProjectGoal::create([
                    //'id'                => $d->id,
                    'project_id'        => $project->id,
                    'name'              => $d->name,
                    'import_id'         => 'reklpro_id_'.$d->id,

                    'call_center_id'    => 1,
                    'geo_id'            =>  $this->get_geo_id($d->geo_id),
                    'price'             => (!empty($d->offer_price)) ? $d->offer_price : 0,
                    'price_currency_id' => $this->get_currency_id($d->offer_price_currency),

                    'action_payment'    => (!empty($d->real_price)) ? $d->real_price : 0,
                    'action_payment_currency_id'  => $this->get_currency_id($d->real_price_currency),
                    'web_master_payment'        => (!empty($d->price)) ? $d->price : 0,
                    'web_master_payment_currency_id'         => $this->get_currency_id($d->price_currency),
                    'additional_payment'  => (!empty($d->delivery_price)) ? $d->delivery_price : 0,
                    
                    'additional_payment_currency_id'       => 1,
                    'is_private'             => $d->private,
                    'min_price'            => (!empty($d->min_price_cart)) ? $d->min_price_cart : 0,
                    'max_price'         => (!empty($d->max_price_cart)) ? $d->max_price_cart : 0                
                ]);
                //$project->geo()->syncWithoutDetaching($this->get_geo_id($d->geo_id));
            }
        });
    }

    protected function migrateScripts()
    {
        $this->import('crm_scripts', 'project_goal_scripts', function ($d) {                   

            if(isset($this->project_info['geo'][$d->project_id])){
                $project_goal = ProjectGoal::where([['project_id',$this->project_info['new_id'][$d->project_id]],['geo_id', $this->project_info['geo'][$d->project_id]]])->first();

                if($project_goal){
                    $this->project_goal_info[$d->id] = $project_goal->id;
                    $project_goal_script    = ProjectGoalScript::create([
                        'id'                     => $d->id,
                        'project_goal_id'        => $project_goal->id,
                        'name'                   => $d->name,
                        'link'                   => 'scripts/old/'.$d->project_id.'/script/'.$d->link,
                        'status'                 => ($d->status=='1')?true:false,
                        'views'                  => 1
                    ]);                
                }
            }
        });
    }

    protected function addGeoToProject()
    {
        $projectGoals = ProjectGoal::all();

        foreach ($projectGoals as $goal) {
            $project = Project::find($goal['project_id']);    
            $project->geo()->syncWithoutDetaching($goal['geo_id']);
        }

        
    }

    function get_currency_id($rekl_currency_id) {        
        $currencies = array(
            "0" => 1,   //руб
            "1" => 2,   //тенге
            "2" => 3,   //грн
            "3" => 4,   //$
            "4" => 5,   //сом
            "5" => 6,   //р
            "6" => 7,   //манат
            "14" => 8,  //драм
            "15" => 9,  //сум
            "17" => 10, //€
            "20" => 11  //₦
        );
        return $currencies[$rekl_currency_id];
    }

    function get_geo_id($rekl_geo_id) {
        $geos = array(
            "0" => 180, //RU
            "1" => 34,  //BY
            "2" => 116, //KZ
            "3" => 218, //UA
            "4" => 107, //KG
            "6" => 17,  //AZ
            "11" => 127,  //LV
            "14" => 8,  //AM
            "15" => 222, //UZ
            "16" => 238, //WW
            "17" => 102, //IT
            "18" => 62, //ES
            "19" => 52, //DE
            "20" => 156 //NG
        );
        if(isset($geos[$rekl_geo_id])){
            return $geos[$rekl_geo_id];
        }else{
            return 0;
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

    function get_geo_code($rekl_geo_id) {
        $geos = array(
            "0" => 'ru', //RU
            "1" => 'by',  //BY
            "2" => 'kz', //KZ
            "3" => 'ua', //UA
            "4" => 'kg', //KG
            "6" => 'az',  //AZ
            "11" => 'lv',  //AZ
            "14" => 'am',  //AM
            "15" => 'uz', //UZ
            "16" => 'ww', //UZ
            "17" => 'it', //IT
            "18" => 'es', //ES
            "19" => 'de', //DE
            "20" => 'ng' //NG
        );
        return $geos[$rekl_geo_id];
    }

    function get_all_rekl_geo() {
        return ['ru','by','bl','kz','ua','kg','az','lv','am','uz','ww','it','es','de','ng'];            
    }

    protected function migrateProjects()
    {
        $this->import('crm_projects', 'projects', function ($d) {                        

            $new_import_id = '';

            $big_geos = [' RU',' BY',' BL',' KZ',' UA',' KG',' AZ',' LV',' AM',' UZ',' WW',' IT',' ES',' DE',' NG'];           
            $lil_geos = [' ru',' by',' bl',' kz',' ua',' kg',' az',' lv',' am',' uz',' ww',' it',' es',' de',' ng'];

            $geos = $this->get_all_rekl_geo();            

            $name = str_replace($lil_geos, '', $d->name);                    
            $name = trim(str_replace($big_geos, '', $name));

            if (stripos($d->import_id, 'reklpro_') !== false) {
                foreach ($geos as $geo) {
                    if (stripos($d->import_id, 'reklpro_'.$geo) !== false) {
                        $new_import_id = str_replace('_'.$geo, '', $d->import_id);                    
                        $this->project_info['geo'][$d->id] = $this->get_crm_geo_id_by_code($geo);                        
                    }                    
                }
            }                    

            $can_save = true;

            if (!empty($new_import_id)) {                
                if(!isset($this->project_info['check'][$new_import_id])){
                    $this->project_info['check'][$new_import_id] = $new_import_id;                                                        
                }else{
                    $this->project_info['new_id'][$d->id] = $this->project_info['doubles'][$new_import_id];    
                    $can_save = false;
                }                
            }        

            if($can_save){
                $id = Project::create([
                    //'id'                => $d->id,
                    'title'             => $name,
                    'organization_id'   => ($this->org_seed == 'no')?$d->id_organization:2,
                    'description'       => $d->desc,

                    'name_for_client'   => $d->name_for_client,
                    'sms_sender'        => $d->sms_sender,
                    'hold'              => (!empty($d->hold)) ? $d->hold : null,
                    'url'               => (!empty($d->url)) ? $d->url : null,

                    'is_private'        => (!empty($d->is_private)) ? $d->is_private : false,
                    'is_call_tracking'  => (!empty($d->is_call_tracking)) ? $d->is_call_tracking : false,
                    'is_authors'        => (!empty($d->is_authors)) ? $d->is_authors : false,
                    'is_resale'         => (!empty($d->is_resale)) ? $d->is_resale : false,
                    'is_postcode_info'  => (!empty($d->is_postcode_info)) ? $d->is_postcode_info : false,
                                
                    'category_id'       => (!empty($d->category_id)) ? $d->category_id : null,
                    'image'             => (!empty($d->image)) ? $d->image : null,
                    'gender'            => (!empty($d->sex)) ? $d->sex : 0,
                    'postclick'         => (!empty($d->postclick)) ? $d->postclick : 30,

                    'age'               => (!empty($d->age)) ? $d->age : [18,40],
                    'import_id'         => (!empty($new_import_id)) ? $new_import_id : $d->import_id,

                    'name_en'           => (!empty($d->name_en)) ? $d->name_en : null,
                    'countries'         => (!empty($d->countries)) ? $d->countries : null,
                    //'sex'               => (!empty($d->sex)) ? $d->sex : 0,
                    'project_category_kc_id'       => (!empty($d->kc_category)) ? $d->kc_category : 0,                                
                ])->id;
                $this->project_info['new_id'][$d->id] = $id;
                $this->project_info['doubles'][$new_import_id] = $id;
            }

        });
    }

    protected function migrateOrderImportIds()
    {
        $this->import('crm_leads_import_ids', 'order_import_ids', function ($d) {
            $orderImportId    = OrderImportId::create([
                'id'                => $d->id,
                'order_id'          => $d->order_id,
                'import_id'         => $d->import_id
            ]);
        });
    }

    protected function migrateCalls()
    {
        $this->import('crm_calls', 'calls', function ($d) {

            Call::create([
                'id'                    => $d->id,
                'organization_id'       => (!empty($d->id_organization)) ? $d->id_organization : null,

                'queue_id'              => (int)$d->id_queue,
                'step_id'               => (int)$d->id_step,
                'order_id'              => (int)$d->order_id,

                'weight'                => (!empty($d->weight)) ? $d->weight : null,

                'call_type'             => (!empty($d->call_type)) ? $d->call_type : '',
                'sip'                   => (int)$d->sip,
                'phone'                 => $d->phone,
                'dst'                   => (!empty($d->dst)) ? $d->dst : 0,

                'record_link'           => (!empty($d->record_link)) ? $d->record_link : 0,
                'record_time'           => $d->record_time,

                'time'                  => Carbon::createFromTimestamp($d->time)->format('Y-m-d H:i:s'),
                'billing_time'          => $d->billing_time,
                'duration_time'         => $d->duration_time,

                'disposition'           => $d->disposition,

                // 'addmember'             => (int)$d->addmember,
                // 'agentdump'             => (int)$d->agentdump,
                // 'agentlogin'            => (int)$d->agentlogin,
                // 'agentlogoff'           => (int)$d->agentlogoff,
                // 'completeagent'         => (int)$d->completeagent,
                // 'completecaller'        => (int)$d->completecaller,
                // 'configreload'          => (int)$d->configreload,
                // 'connect'               => (int)$d->connect,
                // 'enterqueue'            => (int)$d->enterqueue,
                // 'exitempty'             => (int)$d->exitempty,
                // 'caexitwithkeyll'       => (!empty($d->caexitwithkeyll)) ? $d->caexitwithkeyll : 0,
                // 'exitwithtimeout'       => (int)$d->exitwithtimeout,
                // 'ringnoanswer'          => (int)$d->ringnoanswer,
                // 'abandon'               => (int)$d->abandon,
            ]);
        }, false);
    }

    protected function migrateCallStatuses()
    {
        $this->import('crm_call_status_list', 'call_statuses', function ($d) {

            CallStatus::create([
                'id'                    => $d->id,
                'call_id'               => $d->call_id,

                'status'                => $d->call_status,
                'agent'                 => $d->agent,               

                'time'                  => Carbon::createFromTimestamp($d->status_time)->format('Y-m-d H:i:s'),                
            ]);
        }, false);
    }

    protected function migrateSales()
    {
        $this->import('crm_sales', 'sales', function ($d) {

            if($d->is_additional=='1') {
                $upsale = 1;
            }else if($d->is_additional_lvl_2=='1'){
                $upsale = 2;
            }else{
                $upsale = 0;
            }

            Sale::create([
                'id'                => $d->id,
                'uniqued_import_id' => (!empty($d->uniqued_import_id)) ? $d->uniqued_import_id : '',
            
                'product_code'      => (!empty($d->code_product)) ? $d->code_product : '',
               
                'order_id'          => $d->id_lead,
                'product_id'        => $d->id_product,
              
                'comment'           => (!empty($d->comment)) ? $d->comment : null,
                'name'              => (!empty($d->name)) ? $d->name : '',
              
                'product_price'     => (!empty($d->price)) ? $d->price : 0,
                'price'             => $d->cost_price,
                'prime_price'       => $d->price_prime,
                
                'cost_price'        => (!empty($d->cost_price)) ? $d->cost_price : 0,
                'is_cart'           => (!empty($d->is_cart)) ? $d->is_cart : 0,

                'upsale'            => $upsale,
                
                'upsale_user_id'    => ($d->is_additional!=0 || $d->is_additional_lvl_2!=0)?$d->autor_additional:0,
                'user_id'           => $d->autor_additional,
                
                'lead_id'           => (!empty($d->lead_id)) ? $d->lead_id : 0,

                'weight'            => $d->weight,
                'article'           => (!empty($d->article)) ? $d->article : "",
                
                'quantity'          => $d->quantity,
                'quantity_price'    => $d->quantity_price,
                'quantity_pay'      => (!empty($d->quantity_pay)) ? $d->quantity_pay : 0
            ]);
        });
    }

    protected function migrateUsers()
    {
        $pass   = Hash::make('111111');
        $this->import('crm_users', 'users', function($d) use ($pass){
            $user    = User::create([
                'id'                => $d->id,
                'organization_id'   => $d->role_id,
                'login'             => $d->login,
                'first_name'        => $d->first_name,
                'last_name'         => $d->last_name,
                'middle_name'       => $d->middle_name,
                'phone'             => $d->phone,
                'phone_office'      => $d->office_phone,
                'is_work'           => $d->is_work,
                'out_calls'         => $d->out_calls,
                'ip'                => $d->ip,
                'last_online'       => Carbon::createFromTimestamp($d->last_online)->format('Y-m-d H:i:s'),
                'speaker_status'    => $d->speaker_status,
                //'mail'              => ($d->mail == '') ? null : $d->mail,
                'password'          => $pass
            ]);
        });

        $data   = [
            'login'             => 'root',
            'password'          => $pass,
            'organization_id'   => 1
        ];

        $user   = User::create($data);
    }

//    public function migratePhones()
//    {
////        $pass   = Hash::make('111111');
//        DB::connection('crmka_old')
//            ->table('crm_phone_trunks')
//            ->orderBy('id')
//            ->chunk(100, function ($data) {
//
//                foreach ($data as $d) {
//                    Phone::create([
//                        'phone' => $d->id
//                    ]);
//                }
//            });
//
//        $max    = DB::table('phones')->max('id') + 1;
//        DB::statement('ALTER SEQUENCE phones_id_seq RESTART WITH ' . $max . ';');
//    }

    protected function migrateSites()
    {
        $this->import('crm_web_sites', 'sites', function($d) {
            if(isset($this->project_info[$d->project_id])){
                    $project_id = $this->project_info[$d->project_id];
            }else{
                    $project_id = $d->project_id;    
            }
            $site    = Site::create([
                'id'                => $d->id,
                'title'             => $d->title,
                'description'       => $d->desc,
                'organization_id'   => $d->id_organization,
                'project_id'        => $project_id,
                'url'               => $d->url
            ]);
        });
    }

    protected function migrateProjectPages()
    {
        $this->import('crm_web_sites', 'project_page', function($d) {            
            
            $new_import_id = '';

            $geos = $this->get_all_rekl_geo();

            if (stripos($d->import_id, 'reklpro_') !== false) {
                foreach ($geos as $geo) {
                    if (stripos($d->import_id, 'reklpro_'.$geo) !== false) {
                        $new_import_id = str_replace('_'.$geo, '', $d->import_id);                    
                    }                    
                }
            }                    

            $can_save = true;

            if (!empty($new_import_id)) {                
                if(!isset($this->project_page_info['check'][$new_import_id])){
                    $this->project_page_info['check'][$new_import_id] = $new_import_id;                                                        
                }else{
                    $can_save = false;
                }                
            }  

            if(isset($this->project_info['new_id'][$d->project_id])){
                    $project_id = $this->project_info['new_id'][$d->project_id];
            }else{
                    $project_id = 1;    
            }

            if($can_save){
                $geos = ['RU','BY','KZ','UA','KG','AZ','LV','AM','UZ','WW','IT','ES','DE','NG'];

                $new_title = $d->title;

                foreach ($geos as $geo) {            
                    if (stripos($d->title, $geo.'_') !== false) {
                        $new_title = str_replace($geo.'_', '', $d->title);                    
                    }                                
                }

                $organization_id = ($this->org_seed == 'no')?$d->id_organization:2;

                $this->project_page_info['new_id'][$d->id]    = ProjectPage::create([
                    //'id'                => $d->id,
                    'name'              => $new_title,
                    'link'              => $d->url,                
                    'project_id'        => $project_id,
                    'organization_id'   => $organization_id == 0 ? 1 : $organization_id,
                    'import_id'         => (!empty($new_import_id)) ? $new_import_id : $d->import_id
                ])->id;
            }
        });
    }

    protected function migrateComments()
    {
        $this->import('crm_comments', 'comments', function($d) {

            if($d->comment_type=="orders"){
                $comment    = Comment::create([
                    'id'                => $d->id,
                    'text'              => $d->content,
                    'order_id'          => $d->id_obj,                
                    'user_id'           => intval($d->id_user),
                    'organization_id'   => $d->id_organization,
                    'created_at'        => ((int)$d->create_date) ? Carbon::createFromTimestamp($d->create_date)->format('Y-m-d H:i:s') : null,
                ]);
            }
        });
    }

    protected function migrateDeliveryTypes()
    {
        $this->import('crm_delivery_types', 'delivery_types', function($d) {            
            $site    = DeliveryType::create([
                'id'                => $d->id,
                'organization_id'   => ($this->org_seed == 'no')?$d->id_organization:2,
                'name'              => $d->name,                
                'price'             => $d->price,
                'is_work'           => $d->is_work ? true : false,
                'priority'          => $d->priority,
                'postcode_info'     => $d->postcode_info
            ]);
        });
    }

    protected function migrateOrders()
    {
        $this->import('crm_leads', 'orders', function($data) {

            $numbers    = array_only((array)$data, ['phone', 'phone_2', 'phone_3']);
            $numbers    = array_values($numbers);

            $numbers    = array_filter($numbers, function($el){
                return !empty((string)$el);
            });

            $dop_info = null;
            if (is_object(json_decode($data->dop_info))) 
            { 
                $dop_info = $data->dop_info;
            }            

            if(isset($this->project_goal_info[$data->script_id])){                
                $project_goal_id = $this->project_goal_info[$data->script_id];       
                $project_goal_script_id = $data->script_id;
            }else{
                $project_goal_id = null;                
                $project_goal_script_id = null;
            }            

            $order  = Order::create([
                'id'                            => $data->id,
                'key'                           => !(empty($data->key_lead)) ? $data->key_lead : null,
                'import_id'                     => (!empty($data->import_id)) ? $data->import_id : null,

                'import_webmaster_id'           => $data->id_webmaster,
                //'import_webmaster_transit_id'   => (!empty($data->webmaster_transit_id)) ? $data->webmaster_transit_id : null,
              
                'request_hash'                  => !(empty($data->request_hash)) ? $data->request_hash : null,
                'api_key'                       => !(empty($data->api_key)) ? $data->api_key : null,
                'type'                          => $data->type,
                'organization_id'               => $data->id_organization,
                'dial_step'                     => $data->dial_step,
                'dial_time'                     => ((int)$data->dial_time) ? Carbon::createFromTimestamp($data->dial_time)->format('Y-m-d H:i:s') : null,
                'delivery_types_id'             => (!empty($data->delivery_type)) ? $data->delivery_type : null,
                'delivery_types_price'          => (!empty($data->delivery_price)) ? $data->delivery_price : null,
               
                'delivery_date_finish'          => ((int)$data->delivery_time) ? Carbon::createFromTimestamp($data->delivery_time)->format('Y-m-d H:i:s') : null,
                'delivery_time_1'               => $data->delivery_time_1,
                'delivery_time_2'               => $data->delivery_time_2,
                              
                'phones'                        => array_values($numbers),
                'country_code'                  => $data->country_code,
                'client_name'                   => $data->client_name,

                'full_address'                  => (!empty($data->full_address)) ? $data->full_address : null,
                'region'                        => (!empty($data->region)) ? $data->region : null,
                'area'                          => (!empty($data->area)) ? $data->area : null,
                'city'                          => (!empty($data->sity)) ? $data->sity : null,
                'street'                        => (!empty($data->street)) ? $data->street : null,
                'home'                          => (!empty($data->home)) ? $data->home : null,
                'room'                          => (!empty($data->room)) ? $data->room : null,
                'housing'                       => (!empty($data->housing)) ? $data->housing : null,
                'postcode'                      => (!empty($data->postcode)) ? $data->postcode : null,

                'warehouse'                     => (!empty($data->warehouse)) ? $data->warehouse : null,
                'warehouse_id'                  => (!empty($data->warehouse_id)) ? $data->warehouse_id : null,

                'operator_id'                   => (!empty($data->id_caller)) ? $data->id_caller : null,
                'client_email'                  => (!empty($data->mail_client)) ? $data->mail_client : null,


                'info'                          => (!empty($dop_info)) ? $dop_info : null,
            
                'track_number'                  => (!empty($data->track_number)) ? $data->track_number : null,
                'site_order_id'                 => (!empty($data->site_order_id)) ? $data->site_order_id : null,
                'delivery_price'                => (!empty($data->delivery_price)) ? $data->delivery_price : null,
                'products_total'                => (!empty($data->products_total)) ? $data->products_total : null,

                'upsale1'                       => (!empty($data->upsale_lvl_1)) ? $data->upsale_lvl_1 : null,
                'upsale2'                       => (!empty($data->upsale_lvl_2)) ? $data->upsale_lvl_2 : null,

                //'cost_main'                     => (!empty($data->cost_main)) ? $data->cost_main : null,
                'status_old_crm'                => (!empty($data->status_old_crm)) ? $data->status_old_crm : null,
                'status_1c_1'                   => (!empty($data->status_1c_1)) ? $data->status_1c_1 : null,
                'status_1c_2'                   => (!empty($data->status_1c_2)) ? $data->status_1c_2 : null,
                'status_1c_3'                   => (!empty($data->status_1c_3)) ? $data->status_1c_3 : null,

                'responsible_id'                => (!empty($data->id_responsible)) ? $data->id_responsible : null,
                'gasket_id'                     => (!empty($data->id_gasket)) ? $data->id_gasket : null,
                'webmaster_id'                  => (!empty($data->id_webmaster)) ? $data->id_webmaster : null,
                'flow_id'                       => (!empty($data->id_flow)) ? $data->id_flow : null,

                'real_profit'                   => (!empty($data->real_profit)) ? $data->real_profit : null,
                'second_id'                     => (!empty($data->id_second)) ? $data->id_second : null,
                'profit'                        => (!empty($data->profit)) ? $data->profit : null,
                'project_goal_id'               => $project_goal_id,
                'project_goal_script_id'        => $project_goal_script_id,

                'time_zone'                     => (!empty($data->time_zone)) ? $data->time_zone : null,
                'barcode'                       => (!empty($data->barcode)) ? $data->barcode : null,
                'phone_country'                 => (!empty($data->phone_country)) ? $data->phone_country : null,
                'referer'                       => (!empty($data->referer)) ? $data->referer : null,

                'transit_webmaster_id'          => (!empty($data->webmaster_transit_id)) ? $data->webmaster_transit_id : null,
                'webmaster_type'                => (!empty($data->webmaster_type)) ? $data->webmaster_type : null,
                'top_t'                         => (!empty($data->top_t)) ? $data->top_t : null,
                'source_id'                     => (!empty($data->source_id)) ? $data->source_id : null,

                'sex_id'                        => (!empty($data->sex_id)) ? $data->sex_id : null,
                'device_id'                     => (!empty($data->device_id)) ? $data->device_id : null,
                'age_id'                        => (!empty($data->age_id)) ? $data->age_id : null,
                'comment_client'                => (!empty($data->comment_client)) ? $data->comment_client : null,

                'arrival_office_date'           => ((int)$data->arrival_office_date) ? Carbon::createFromTimestamp($data->arrival_office_date)->format('Y-m-d H:i:s') : null,
                'is_unload'                      => (!empty($data->is_unload)) ? $data->is_unload : null,
                'key_lead'                      => (!empty($data->key_lead)) ? $data->key_lead : null,

                'ordered_at'                    => ((int)$data->order_date) ? Carbon::createFromTimestamp($data->order_date)->format('Y-m-d H:i:s') : null,
                'created_at'                    => ((int)$data->create_date) ? Carbon::createFromTimestamp($data->create_date)->format('Y-m-d H:i:s') : null,
            ]);


            /*if(!empty($data->delivery_type)) { 
                $delivery = DeliveryType::find($data->delivery_type);
                $order->delivery_types()->associate($delivery);              
            }*/

            /*$siteIds    = trim($data->site, '.');
            if(!empty($siteIds)) {
                $siteIds    = (array)explode('.', $siteIds);
                $sites      = Site::findMany($siteIds);
                if(count($sites)) {
                    $order->sites()->attach($sites);
                }
            }*/

            $projectPageIds    = trim($data->site, '.');
            if(!empty($projectPageIds)) {                
                if(isset($this->project_page_info['new_id'][$projectPageIds])){
                    $projectPageIds = (array)$this->project_page_info['new_id'][$projectPageIds];
                }else{
                    $projectPageIds = (array)explode('.', $projectPageIds);    
                }
                $projectPages      = ProjectPage::findMany($projectPageIds);
                if(count($projectPages)) {
                    $order->project_pages()->attach($projectPages);
                }
            }

            $projectIds    = trim($data->project, '.');
            if(!empty($projectIds)) {
                if(isset($this->project_info['new_id'][$projectIds])){
                    $projectIds = (array)$this->project_info['new_id'][$projectIds];
                }else{
                    $projectIds = (array)explode('.', $projectIds);    
                }
                
                $projects   = Project::findMany($projectIds);
                if(count($projects)) {
                    $order->projects()->attach($projects);
                }
            }

            $statusesIds = [];            
            if($data->status_group_1){                
                $statusesIds[$data->status_group_1]=[                                        
                    'user_id' => $data->autor_status_1,
                    'status_type' => 1,
                    'created_at' => Carbon::createFromTimestamp($data->date_status_1)->format('Y-m-d H:i:s')
                ];
            }
            if($data->status_group_2){
                $statusesIds[$data->status_group_2]=[                    
                    'user_id' => $data->autor_status_2,
                    'status_type' => 2,
                    'created_at' => Carbon::createFromTimestamp($data->date_status_2)->format('Y-m-d H:i:s')
                ];
            }
            if($data->status_group_3){
                $statusesIds[$data->status_group_3]=[
                    'user_id' => $data->autor_status_3,
                    'status_type' => 3,
                    'created_at' => Carbon::createFromTimestamp($data->date_status_3)->format('Y-m-d H:i:s')
                ];
            }
            if($data->status_group_4){
                $statusesIds[$data->status_group_4]=[                    
                    'user_id' => $data->autor_status_4,
                    'status_type' => 4,
                    'created_at' => Carbon::createFromTimestamp($data->date_status_4)->format('Y-m-d H:i:s')
                ];
            }
            if($data->status_group_5){
                $statusesIds[$data->status_group_5]=[                    
                    'user_id' => $data->autor_status_5,
                    'status_type' => 5,
                    'created_at' => Carbon::createFromTimestamp($data->date_status_5)->format('Y-m-d H:i:s')
                ];
            }
            if($data->status_group_6){
                $statusesIds[$data->status_group_6]=[                    
                    'user_id' => $data->autor_status_6,
                    'status_type' => 6,
                    'created_at' => Carbon::createFromTimestamp($data->date_status_6)->format('Y-m-d H:i:s')
                ];
            }
            if($data->status_group_7){
                $statusesIds[$data->status_group_7]=[                    
                    'user_id' => $data->autor_status_7,
                    'status_type' => 7,
                    'created_at' => Carbon::createFromTimestamp($data->date_status_7)->format('Y-m-d H:i:s')
                ];
            }
            if($data->status_group_8){
                $statusesIds[$data->status_group_8]=[                    
                    'user_id' => $data->autor_status_8,
                    'status_type' => 8,
                    'created_at' => Carbon::createFromTimestamp($data->date_status_8)->format('Y-m-d H:i:s')
                ];
            }
            if($data->status_group_9){
                $statusesIds[$data->status_group_9]=[                    
                    'user_id' => $data->autor_status_9,
                    'status_type' => 9,
                    'created_at' => Carbon::createFromTimestamp($data->date_status_9)->format('Y-m-d H:i:s')
                ];
            }
            if($data->status_group_10){
                $statusesIds[$data->status_group_10]=[                    
                    'user_id' => $data->autor_status_10,
                    'status_type' => 10,
                    'created_at' => Carbon::createFromTimestamp($data->date_status_10)->format('Y-m-d H:i:s')
                ];
            }
            /*if($data->status_group_11){
                $statusesIds[$data->status_group_11]=[                    
                    'user_id' => $data->autor_status_11,
                    'status_type' => 11,
                    'created_at' => Carbon::createFromTimestamp($data->date_status_11)->format('Y-m-d H:i:s')
                ];
            }
            if($data->status_group_12){
                $statusesIds[$data->status_group_12]=[                    
                    'user_id' => $data->autor_status_12,
                    'status_type' => 12,
                    'created_at' => Carbon::createFromTimestamp($data->date_status_12)->format('Y-m-d H:i:s')
                ];
            }
            if($data->status_group_13){
                $statusesIds[$data->status_group_13]=[                    
                    'user_id' => $data->autor_status_13,
                    'status_type' => 13,
                    'created_at' => Carbon::createFromTimestamp($data->date_status_13)->format('Y-m-d H:i:s')
                ];
            }
            if($data->status_group_14){
                $statusesIds[$data->status_group_14]=[                    
                    'user_id' => $data->autor_status_14,
                    'status_type' => 14,
                    'created_at' => Carbon::createFromTimestamp($data->date_status_14)->format('Y-m-d H:i:s')
                ];
            }
            if($data->status_group_15){
                $statusesIds[$data->status_group_15]=[                    
                    'user_id' => $data->autor_status_15,
                    'status_type' => 15,
                    'created_at' => Carbon::createFromTimestamp($data->date_status_15)->format('Y-m-d H:i:s')
                ];
            }          */

            if(!empty($statusesIds)) {                
                $order->statuses()->attach($statusesIds);                
            }

        });
    }

    protected function migrateProducts()
    {
        $this->import('crm_products', 'products', function ($data) {
            Product::create([
                'id'                    => $data->id,
                'code_product'          => $data->code_product,
                'uniqued_import_id'     => $data->uniqued_import_id,
                'organization_id'       => ($this->org_seed == 'no')?$data->id_organization:2,
                //'cat_id'                => $data->cat_id,
                'article'               => $data->article,
                'img'                   => '[]',
                //'parent_project'        => $data->parent_project,
                //'parent_site'           => $data->parent_site,
                'name'                  => $data->name,
                'price_cost'            => $data->price_cost,
                'price_online'          => $data->price_online,
                'price_prime'           => $data->price_prime,
                'weight'                => $data->weight,
                'desc'                  => $data->desc,
                'script'                => $data->script,
                'basic_unit'            => $data->basic_unit,
                'nabor'                 => $data->nabor,
                'service'               => $data->service,
                'complect'              => $data->complect,
                'basic_unit_seat'       => $data->basic_unit_seat,
                'is_work'               => $data->is_work=='1' ? true : false
            ]);

        });
    }
    protected function migratePhoneCodes()
    {
        $this->import('crm_phone_codes', 'phone_codes', function ($d) {
            $PhoneCodes   = PhoneCodes::create([
                'id'                => $d->id,
                'country_code'      => $d->country_code,
                'city_code'         => $d->city_code,
                'original_code'     => $d->code_original,
                'provider'          => $d->provider,
                'region'            => $d->region,
                'time_zone'         => $d->time_zone,
                'type'              => $d->type
            ]);
        });
    }

    protected function migrateStatuses()
    {
        $this->import('crm_status', 'statuses', function ($data) {

            if($data->type=='status_group_1') {
                $type = 1;
            }else if($data->type=='status_group_2'){
                $type = 2;
            }else if($data->type=='status_group_3'){
                $type = 3;
            }else if($data->type=='status_group_4'){
                $type = 4;
            }else if($data->type=='status_group_5'){
                $type = 5;
            }else if($data->type=='status_group_6'){
                $type = 6;
            }else if($data->type=='status_group_7'){
                $type = 7;
            }else if($data->type=='status_group_8'){
                $type = 8;
            }else if($data->type=='status_group_9'){
                $type = 9;
            }else if($data->type=='status_group_10'){
                $type = 10;
            }else if($data->type=='status_group_11'){
                $type = 11;
            }else if($data->type=='status_group_12'){
                $type = 12;
            }else if($data->type=='status_group_13'){
                $type = 13;
            }else if($data->type=='status_group_14'){
                $type = 14;
            }else if($data->type=='status_group_15'){
                $type = 15;
            }

            
            if($data->update_unix_date == '0'){
                $update_date = $data->create_date;
            } else {
                $update_date = $data->update_date;
            }

            Status::create([
                'id'                    => $data->id,
                'parent_id'             => $data->parent_id,
                'organization_id'       => ($this->org_seed == 'no')?$data->id_organization:1,
                'name'                  => $data->name,
                'title'                 => $data->name,
                'desc'                  => $data->desc,
                'is_work'               => $data->is_work,
                'type'                  => $type,
                'color'                 => $data->style_background,
                'sort'                  => $data->sort,
                'created_at'            => $data->create_date,
                'updated_at'            => $update_date,
            ]);

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
                ->where('id_organization', 67)
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
            }else{
                DB::connection('crmka_old')
                ->table($tableFrom)
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
