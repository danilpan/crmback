<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAtsGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ats_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ats_id')->unsigned();
            $table->string('name', 80);
            $table->string('description', 255)->nullable();
            $table->boolean('is_work')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ats_groups');
    }
}
