<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePostcodeInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('postcode_infos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('postcode', 100);
            $table->integer('delivery_type_id');
            $table->string('comment', 255);
            $table->integer('time');
            $table->decimal('price',8,2);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::dropIfExists('postcode_infos');
    }
}
