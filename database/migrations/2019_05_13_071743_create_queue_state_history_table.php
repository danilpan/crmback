<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQueueStateHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('queue_state_history', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('queue_id');
            $table->integer('main');
            $table->integer('online');
            $table->integer('speak');
            $table->integer('option_in_calls');
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
        Schema::dropIfExists('queue_state_history');
    }
}
