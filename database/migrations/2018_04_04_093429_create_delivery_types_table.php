<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeliveryTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_types', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('organization_id');
            $table->string('name', 100);
            $table->decimal('price',8,2);
            $table->boolean('is_work')->default('false');
            $table->boolean('is_show')->default('true');
            $table->integer('priority')->nullable();
            $table->integer('sort')->nullable();
            $table->text('postcode_info')->nullable(); // Подсказки для операторов
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
        Schema::dropIfExists('delivery_types');
    }
}
