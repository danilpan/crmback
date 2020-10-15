<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLastStatusToAtsQueues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ats_queues', function (Blueprint $table) {
            $table->integer('last_status')->unsigned()->nullable();
            $table->foreign('last_status')->references('id')->on('statuses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ats_queues', function (Blueprint $table) {
            $table->dropForeign('last_status_foreign');
            $table->dropColumn('last_status');
        });
    }
}
