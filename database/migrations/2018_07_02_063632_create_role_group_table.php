<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoleGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('role_group', function (Blueprint $table) {
            $table->increments('id');
            
            $table->string('name', 255); 
            $table->string('description', 255)->nullable(); 

            $table->integer('creator_organization_id')->unsigned()->nullable();
            $table->foreign('creator_organization_id')->references('id')->on('organizations'); 

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
        Schema::dropIfExists('role_group');
    }
}
