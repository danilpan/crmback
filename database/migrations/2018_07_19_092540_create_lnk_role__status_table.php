<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLnkRoleStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lnk_role__status', function (Blueprint $table) {
          
            $table->increments('id');
            
            $table->integer('role_id')->comment('Id-роли');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');

            $table->integer('status_id')->comment('Id-статуса');
            $table->foreign('status_id')->references('id')->on('statuses')->onDelete('cascade');

            $table->boolean('is_view')->default(0)->comment('Просморт');
            $table->boolean('is_can_set')->default(0)->comment('Возможность установки');

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
        Schema::dropIfExists('lnk_role__status');
    }
}
