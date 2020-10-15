<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLnkRoleEntityParamTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lnk_role__entity_param', function (Blueprint $table) {
            $table->increments('id');
            
            $table->integer('role_id')->comment('Id-роли');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->integer('entity_param_id')->comment('Id-праметра сущности');
            $table->foreign('entity_param_id')->references('id')->on('entity_params')->onDelete('cascade');

            $table->integer('entity_id')->comment('Id-сущности');
            $table->foreign('entity_id')->references('id')->on('entities')->onDelete('cascade');

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
        Schema::dropIfExists('lnk_role__entity_param');
    }
}
