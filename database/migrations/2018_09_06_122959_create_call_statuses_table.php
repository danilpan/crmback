<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCallStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('call_statuses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('call_id');
            $table->string('status');
            $table->string('agent', 12)->nullable();
            $table->timestamp('time');            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('call_statuses');
    }
}
