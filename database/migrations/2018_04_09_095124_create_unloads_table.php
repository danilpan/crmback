<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUnloadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unloads', function (Blueprint $table) {

            $table->increments('id');
            $table->integer('organization_id')->nullable(); // Организация
            $table->string('name', 100); // название выгрузки
            $table->string('comment')->nullable(); // Комментарий к выгрузке
            $table->text('api_key', 500)->nullable(); // Api-ключ выгрузки
            $table->text('config')->nullable(); // Данные с фильтрами
            $table->boolean('is_work')->default(true); // Выкл/вкл выгрузки
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
        Schema::dropIfExists('unloads');
    }
}
