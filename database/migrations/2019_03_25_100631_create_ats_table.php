<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAtsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ats', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ip', 15);
            $table->string('key', 32);
            $table->string('name', 255);
            $table->string('description', 255)->nullable();
            $table->boolean('is_work')->default(false);
            $table->boolean('is_default')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ats');
    }
}
