<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOutRoutesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('out_routes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 80);
            $table->string('comment', 255)->nullable();
            $table->string('mask', 20);
            $table->tinyInteger('replace_count')->default(0);
            $table->string('prefix', 20)->nullable();
            $table->text('trunks1', 1000);
            $table->text('trunks2', 1000);
            $table->tinyInteger('trunks_p1');
            $table->tinyInteger('trunks_p2');
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
        Schema::dropIfExists('out_routes');
    }
}
