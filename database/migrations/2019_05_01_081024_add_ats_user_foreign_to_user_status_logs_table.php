<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAtsUserForeignToUserStatusLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_status_logs', function (Blueprint $table) {
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
        Schema::table('user_status_logs', function (Blueprint $table) {
            $table->dropForeign('ats_user_id_foreign');
        });
    }
}
