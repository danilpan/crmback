<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSkeletonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('skeleton', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('organization_id')->nullable();
            $table->string('comment', 255)->nullable();
            $table->double('line1')->default(0)->nullable();
            $table->double('line2')->default(0)->nullable();
            $table->double('line3')->default(0)->nullable();
            $table->double('line4')->default(0)->nullable();
            $table->double('line5')->default(0)->nullable();
            $table->double('line6')->default(0)->nullable();
            $table->double('line7')->default(0)->nullable();
            $table->double('line8')->default(0)->nullable();
            $table->double('line9')->default(0)->nullable();
            $table->double('line10')->default(0)->nullable();
            $table->double('line11')->default(0)->nullable();
            $table->double('line12')->default(0)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('skeleton');
    }
}
