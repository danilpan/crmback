<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('group_id')->unsigned();
            $table->foreign('group_id')->references('id')->on('role_group')->onDelete('cascade');
            
            $table->string('name', 255); 
            $table->string('description', 255)->nullable();

            $table->boolean('is_need_display_notify')->default(false); // Необходимы ли оповещения на экране

            $table->integer('creator_organization_id')->unsigned()->nullable();
            $table->foreign('creator_organization_id')->references('id')->on('organizations')->onDelete('cascade');

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
        Schema::dropIfExists('roles');
    }
}
