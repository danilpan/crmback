<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDsdayAndAvrToAtsQueues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ats_queues', function (Blueprint $table) {
            $table->string('avr', 255)->nullable();
            $table->integer('dsday')->unsigned()->nullable();
            $table->foreign('dsday')->references('id')->on('statuses')->onDelete('cascade');
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
            $table->dropForeign('dsday_foreign');
            $table->dropColumn(['dsday', 'avr']);
        });
    }
}
