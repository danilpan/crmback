<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAtsUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ats_users', function (Blueprint $table) {
            $table->increments('id');
            $table->mediumInteger('port')->default(5060);
            $table->string('passwd', 80);
            $table->string('login', 80);
            $table->tinyInteger('max_channels')->unsigned();
            $table->integer('ats_group_id')->unsigned();
            $table->foreign('ats_group_id')->references('id')->on('ats_groups')->onDelete('cascade');
            $table->string('type', 16);
            $table->boolean('is_work')->default(true);
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ats_users');
    }
}
