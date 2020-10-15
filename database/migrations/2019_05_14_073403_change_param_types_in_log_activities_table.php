<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeParamTypesInLogActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('log_activities', function (Blueprint $table) {
            $table->text('url')->change();
            $table->text('referer')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('log_activities', function (Blueprint $table) {
            $table->string('url')->change();
            $table->string('referer')->change();
        });
    }
}
