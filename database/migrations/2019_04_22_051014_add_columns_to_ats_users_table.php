<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToAtsUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ats_users', function (Blueprint $table) {
            $table->string('comment', 255)->nullable();
            $table->boolean('out_calls')->default(false);
            $table->boolean('option_in_call')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ats_users', function (Blueprint $table) {
            $table->dropColumn(['comment', 'out_calls', 'option_in_call']);
        });
    }
}
