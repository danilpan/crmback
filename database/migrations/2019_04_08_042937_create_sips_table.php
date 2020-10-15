<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sips', function (Blueprint $table) {
            $table->increments('id');
            $table->string('host');
            $table->mediumInteger('port');
            $table->string('passwd', 80);
            $table->string('login', 80);
            $table->tinyInteger('max_channels')->unsigned();
            $table->string('template');
            $table->string('connect_type', 5);
            $table->integer('ats_group_id')->unsigned();
            $table->foreign('ats_group_id')->references('id')->on('ats_groups')->onDelete('cascade');
            $table->boolean('is_work')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sips');
    }
}
