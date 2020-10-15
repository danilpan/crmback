<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQueueIdToSipCallerIdsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sip_caller_ids', function (Blueprint $table) {
            $table->integer('ats_queue_id')->unsigned()->nullable();
            $table->foreign('ats_queue_id')->references('id')->on('ats_queues')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sip_caller_ids', function (Blueprint $table) {
            $table->dropForeign('ats_queue_id_foreign');
            $table->dropColumn('ats_queue_id');
        });
    }
}
