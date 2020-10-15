<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectGoalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_goal', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('project_id')->unsigned()->nullable();
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->string('name', 255); 
            $table->string('import_id', 255)->nullable(); 

            $table->integer('call_center_id')->unsigned()->nullable();
            $table->integer('geo_id')->unsigned()->nullable();
	    
            $table->decimal('price',12,2);
            $table->integer('price_currency_id');
        
            $table->decimal('action_payment',12,2);
            $table->integer('action_payment_currency_id');

            $table->decimal('web_master_payment',12,2);
            $table->integer('web_master_payment_currency_id'); 

            $table->boolean('is_private');

            $table->decimal('additional_payment',12,2);
            $table->integer('additional_payment_currency_id');

            $table->decimal('min_price',12,2);
            $table->decimal('max_price',12,2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_goal');
    }
}
