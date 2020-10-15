<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('organization_id')->nullable()->index();
            $table->integer('user_id')->nullable();
            $table->string('title')->nullable();
            $table->text('api')->nullable();
            $table->text('orders_data')->nullable();
            $table->text('orders_fields')->nullable();
            $table->text('order')->nullable();
            $table->text('organizations')->nullable()->comment('Дотсупы к организациям');
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
        Schema::dropIfExists('permissions');
    }
}
