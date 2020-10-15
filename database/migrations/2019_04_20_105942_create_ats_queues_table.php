<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAtsQueuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ats_queues', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type', 32);
            $table->string('name', 80);
            $table->string('comment', 255)->nullable();
            $table->text('steps1');
            $table->text('steps2');
            $table->time('off_time1');
            $table->time('off_time2');
            $table->tinyInteger('how_call');
            $table->string('strategy', 32)->default('random');
            $table->boolean('check_wbt')->default(0);
            $table->integer('unload_id')->unsigned()->nullable();
            $table->foreign('unload_id')->references('id')->on('unloads')->onDelete('cascade');
            $table->integer('organization_id')->unsigned();
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->integer('ats_group_id')->unsigned();
            $table->foreign('ats_group_id')->references('id')->on('ats_groups')->onDelete('cascade');            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ats_queues');
    }
}