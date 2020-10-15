<?php

namespace App\Models;

use DB;

class Order extends Model
{
    protected $fillable = [
        'client_name',
        'phones',
        'full_address',
        'region',
        'area',
        'city',
        'street',
        'home',
        'housing',
        'room',
        'postcode',
        'delivery_types_id',
        'delivery_types_price',
        'surplus_percent_price',
        'dial_time',
        'delivery_date_finish',
        'warehouse',
        'warehouse_id',
        'organization_id',
        'age_id',
        'key',
        'ordered_at',
        'delivery_time_1',
        'delivery_time_2',
        'time_zone',
        'track_number',
        'manager_id',
        'device_id',
        'project_goal_id',
        'project_goal_script_id',
        'source_id',
        'webmaster_id',
        'import_id',
        'import_webmaster_id',
        'transit_webmaster_id',
        'import_transit_id',
        'import_flow_id',
        'info',
        'is_double',
        'sex_id',
//        'site_product_name',
//        'webmaster_transit_id',
        'site_product_price',
//        'description',
//        'transit_id',
        'country_code',
        'webmaster_type',
        'profit',
        'real_profit',
        'request_hash',
//        'referer',
        'type',
        'is_unload',
        'status_1c_1',
        'status_1c_2',
        'status_1c_3',
        'status_1c_3_time',
        'client_email'
    ];   

    protected $casts    = [
        'phones'        => 'json',
        'dial_time'=> 'datetime',
        'delivery_date_finish'=> 'datetime',
        'ordered_at'    => 'datetime',
        'status_1c_3_time'    => 'datetime',
/*         'date_status_1' => 'datetime',
        'date_status_2' => 'datetime',
        'date_status_3' => 'datetime',
        'date_status_4' => 'datetime',
        'date_status_5' => 'datetime', */
        'delivery_time_1' => 'time',
        'delivery_time_2' => 'time'
    ];

    public function sites()
    {
        return $this->belongsToMany(Site::class);
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class);
    }

    public function project_pages()
    {
        return $this->belongsToMany(ProjectPage::class);
    }

    public function project_goal()
    {
        return $this->belongsTo(ProjectGoal::class);
    }

    public function project_gasket()
    {
        return $this->belongsTo(ProjectGasket::class);
    }

    public function project_products()
    {
        $project = $this->projects()->with(['products' => function($query){
            $query->where('products.is_work', 1);
        }]);
        return $project;
    }

    public function related_products()
    {
        $project = $this->projects()->with(['related_products' => function($query){
            $query->where('products.is_work', 1);
        }]);
        return $project;
    }

    public function delivery_types()
    {
        return $this->belongsTo(DeliveryType::class, 'delivery_types_id');
    }

    public function geo()
    {
        return $this->belongsTo(Geo::class,'country_code','code');
    }

    public function delivery_types_projects()
    {
        return $this->projects();
    }

    public function statuses()
    {
        return $this->belongsToMany(Status::class)->withPivot('user_id', 'created_at');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function operator()
    {
        return $this->belongsTo(
             User::class,
            'operator_id'
        );
    }


    public function manager()
    {
        return $this->belongsTo(
             User::class,
            'manager_id'
        );
    }

    public function order_sender(){
        return $this->belongsTo(OrderSender::class, 'order_sender_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->orderBy('id','DESC');
    }

    public function calls()
    {
        return $this->hasMany(Call::class)->with('call_statuses')->orderBy('time');
    }

    public function dial_steps()
    {
        return $this->hasMany(OrdersDialSteps::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class)->orderBy('id','DESC');
    }

    public function sms()
    {
        return $this->hasMany(Sms::class)->orderBy('id','DESC');
    }

    public function script()
    {
        return $this->belongsTo(ProjectGoalScript::class, 'project_goal_script_id')->with('cross_sales');
    }

    public function history()
    {
        //DB::enableQueryLog();
        //$test =  $this->morphMany(History::class, 'orders', 'reference_table', 'reference_id', 'id')->get();
        //dd(DB::getQueryLog());
        return $this->morphMany(History::class, 'orders', 'reference_table', 'reference_id', 'id')->with('users')->orderBy('id','DESC')->get();
    }

    public function advert_source(){
        return $this->belongsTo(OrderAdvertSource::class, 'source_id');
    }

    public function device_type(){
        return $this->belongsTo(DeviceType::class, 'device_id');
    }

    public function next(){
        // get next user
        return Order::where('id', '>', $this->id)->orderBy('id','asc')->first();

    }
    public  function previous(){
        // get previous  user
        return Order::where('id', '<', $this->id)->orderBy('id','desc')->first();

    }

}
