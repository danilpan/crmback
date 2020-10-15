<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('statuses', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent_id')->default('0'); //ID родительского статуса
            $table->integer('organization_id')->nullable(); //ID организации
            $table->string('name'); //Название
            $table->string('title'); //Название
            $table->string('desc')->nullable(); //Описание
            $table->integer('is_work')->default('0'); //Вкл/выкл
            $table->integer('type'); //Тип (группа статусов)
            $table->string('color')->default('#000000'); //Цвет подсветки
            $table->integer('sort')->default('0'); //Сортировка
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
        Schema::dropIfExists('statuses');
    }
}
