<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEntityParamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entity_params', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->integer('parent_id')->nullable()->unsigned();
            $table->foreign('parent_id')->references('id')->on('entity_params')->onDelete('cascade')->nullable();

            $table->integer('entity_id')->unsigned();
            $table->foreign('entity_id')->references('id')->on('entities')->onDelete('cascade');

            $table->string('name', 255); 
            $table->string('description', 255)->nullable(); 

            $table->string('parameter', 255); 

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
        Schema::dropIfExists('entity_params');
    }
}
