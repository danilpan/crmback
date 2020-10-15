<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePhoneCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('phone_codes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('country_code')->nullable();
            $table->integer('city_code')->nullable();
            $table->integer('original_code')->nullable();
            $table->string('provider')->nullable();
            $table->string('region')->nullable();
            $table->string('time_zone',11)->nullable();
            $table->string('type',50)->nullable();
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
        Schema::dropIfExists('phone_codes');
    }
}
