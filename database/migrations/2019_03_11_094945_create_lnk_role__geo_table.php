<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLnkRoleGeoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lnk_role__geo', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('role_id')->comment('ID-роли');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');

            $table->integer('geo_id')->nullable();
            $table->foreign('geo_id')->references('id')->on('geo')->onDelete('cascade');

            $table->boolean('is_deduct_geo')->nullable()->comment('За вычетом гео');

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
        Schema::dropIfExists('lnk_role__geo');
    }
}
