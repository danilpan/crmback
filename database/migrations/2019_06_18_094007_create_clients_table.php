<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->increments('id');
            $table->string('phone');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_name');
            $table->unsignedInteger('type')->default(2);
            $table->string('iin');
            $table->unsignedInteger('organization_id');
            $table->unsignedInteger('advert_source_id');
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
        Schema::dropIfExists('clients');
    }
}
