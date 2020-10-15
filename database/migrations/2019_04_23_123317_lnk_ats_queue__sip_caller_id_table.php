<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class LnkAtsQueueSipCallerIdTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('ats_queue_agents');
        Schema::create('lnk_ats_queue__sip_caller_id', function (Blueprint $table) {
            $table->integer('ats_queue_id')->unsigned()->nullable();
            $table->foreign('ats_queue_id')->references('id')->on('ats_queues')->onDelete('cascade');
            $table->integer('sip_caller_id_id')->unsigned()->nullable();
            $table->foreign('sip_caller_id_id')->references('id')->on('sip_caller_ids')->onDelete('cascade');
            $table->tinyInteger('sorting');
            $table->boolean('option_in_calls')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lnk_ats_queue__sip_caller_id');
    }
}
