<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserIdToCallStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('call_statuses', function (Blueprint $table) {            
            $table->dropColumn('time');            
        });

        Schema::table('call_statuses', function (Blueprint $table) {
            $table->integer('user_id')->unsigned()->nullable();            
            $table->timestamp('time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
        Schema::table('call_statuses', function (Blueprint $table) {
            $table->dropColumn('user_id');
            $table->dropColumn('time');        
        });

        Schema::table('call_statuses', function (Blueprint $table) {            
            $table->timestamp('time'); 
        });
    }
}
