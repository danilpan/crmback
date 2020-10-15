<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAtsUserIdToSipCallerIdsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sip_caller_ids', function (Blueprint $table) {
            $table->integer('ats_user_id')->unsigned()->nullable();
            $table->foreign('ats_user_id')->references('id')->on('ats_users')->onDelete('cascade');
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
            $table->dropForeign('ats_user_id_foreign');
            $table->dropColumn('ats_user_id');
        });
    }
}
