<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('key')->nullable()->unique();
            $table->string('import_id')->nullable();

            $table->string('webmaster_id')->nullable();
            $table->string('import_webmaster_id')->nullable();
            $table->string('transit_webmaster_id')->nullable();
            
            $table->string('request_hash')->nullable();
            $table->string('api_key')->nullable();
            $table->string('type')->nullable();
            $table->integer('organization_id')->nullable();
            $table->integer('dial_step')->nullable();
            $table->timestamp('dial_time')->nullable();
            $table->integer('delivery_types_id')->nullable();
            $table->decimal('delivery_types_price',12,2)->nullable();

            $table->timestamp('delivery_date_finish')->nullable();

            $table->time('delivery_time_1')->nullable();
            $table->time('delivery_time_2')->nullable();

            $table->text('phones')->nullable();
            $table->string('country_code')->nullable();
            $table->string('client_name')->nullable();
            
            $table->string('full_address')->nullable();
            $table->string('region')->nullable();
            $table->string('area')->nullable();
            $table->string('city')->nullable();
            $table->string('street')->nullable();
            $table->string('home')->nullable();
            $table->string('room')->nullable();
            $table->string('housing')->nullable();
            $table->string('postcode')->nullable();

            $table->string('warehouse')->nullable();
            $table->string('warehouse_id')->nullable();

            $table->integer('operator_id')->nullable();
            $table->string('client_email')->nullable();

            $table->json('info')->nullable();

            $table->string('track_number')->nullable();
            $table->integer('site_order_id')->nullable();
            $table->decimal('delivery_price',12,2)->nullable();
            $table->integer('products_total')->nullable();

            $table->integer('upsale1')->nullable();
            $table->integer('upsale2')->nullable();

            //$table->decimal('cost_main',8,2)->nullable();
            $table->decimal('site_product_price',12,2)->nullable();
            $table->string('status_old_crm')->nullable();
            $table->string('status_1c_1')->nullable();
            $table->string('status_1c_2')->nullable();
            $table->string('status_1c_3')->nullable();

            $table->integer('responsible_id')->nullable();
            $table->integer('gasket_id')->nullable()->index();            
            $table->integer('flow_id')->nullable();
            $table->string('import_flow_id')->nullable();

            $table->decimal('real_profit',12,2)->nullable();
            $table->integer('second_id')->nullable();
            $table->decimal('profit',12,2)->nullable();

            $table->string('time_zone')->nullable();
            $table->integer('barcode')->nullable();
            $table->string('phone_country')->nullable();
            $table->integer('referer')->nullable();

            $table->integer('webmaster_type')->nullable();
            $table->integer('top_t')->nullable();
            $table->integer('source_id')->nullable();

            $table->string('sex_id')->nullable();
            $table->integer('device_id')->nullable();
            $table->integer('age_id')->nullable();
            $table->integer('project_goal_id')->nullable();
            $table->integer('project_gasket_id')->nullable();
            $table->integer('project_goal_script_id')->nullable();            
            $table->text('comment_client')->nullable();
            
            $table->timestamp('arrival_office_date')->nullable();
            $table->integer('is_unload')->nullable();
            $table->string('key_lead')->nullable()->unique();
            
            $table->timestamp('ordered_at')->nullable();

            $table->integer('manager_id')->nullable();

            $table->timestamps();

//            $table->integer('phone_id')->nullable();


//            $table->string('site_product_name')->nullable();
//            $table->string('country_code')->nullable();
//            $table->string('site_product_price')->nullable();
            $table->string('transit_id')->nullable();
            $table->string('import_transit_id')->nullable();
//            $table->string('description')->nullable();

//            $table->integer('site_id')->nullable()->index();
//
//            $table->string('timezone')->nullable();
//            $table->integer('order_date')->nullable();
//            $table->string('delivery_type')->nullable();
//


//            $table->string('order_age')->nullable();
//            $table->string('sales')->nullable();



        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
