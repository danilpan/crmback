<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditSipCallerIdsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sip_caller_ids', function (Blueprint $table) {
            $table->renameColumn('called_id', 'caller_id');
            $table->integer('sip_id')->unsigned()->nullable()->change();
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
            $table->renameColumn('caller_id', 'called_id');
            $table->integer('sip_id')->unsigned()->change();
        });
    }
}
