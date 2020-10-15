<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSmsProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms_providers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('organization_id');
            $table->integer('sms_provider');
            $table->string('name', 255);
            $table->string('comment', 255)->nullable();
            $table->text('data');
            $table->integer('is_work')->default(0);
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
        Schema::dropIfExists('sms_providers');
    }
}
