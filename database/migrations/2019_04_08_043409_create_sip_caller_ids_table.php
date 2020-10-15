<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSipCallerIdsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sip_caller_ids', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sip_id')->unsigned();
            $table->foreign('sip_id')->references('id')->on('sips')->onDelete('cascade');
            $table->string('called_id', 14);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sip_caller_ids');
    }
}
