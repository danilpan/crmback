<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBuyersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('buyers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('client_name', 255);            
            $table->string('email', 255);
            $table->integer('iin');
            $table->string('phone', 14);
            $table->string('phone_2', 25);
            $table->string('phone_3', 25);
            $table->string('phone_country', 255);
            $table->string('full_address', 255);
            $table->string('region', 255);
            $table->string('city', 255);
            $table->string('street', 255);
            $table->string('home', 255);
            $table->string('room', 255);
            $table->string('postcode', 255);
            $table->integer('order_id');
            $table->string('ip', 255);            
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
        Schema::dropIfExists('buyers');
    }
}
