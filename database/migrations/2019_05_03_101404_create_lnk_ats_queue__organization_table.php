<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLnkAtsQueueOrganizationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lnk_ats_queue__organization', function (Blueprint $table) {
            $table->integer('ats_queue_id')->unsigned();
            $table->foreign('ats_queue_id')->references('id')->on('ats_queues')->onDelete('cascade');
            $table->integer('organization_id')->unsigned();
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lnk_ats_queue__organization');
    }
}
