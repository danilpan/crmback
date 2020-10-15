<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderDeliveryInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_delivery_info', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_id'); //ID заказа
            $table->integer('delivery_id')->nullable(); //ID заказа в службе доставке
            $table->string('d_type', 32); //Идентификатор доставки
            $table->string('track_number', 255)->nullable();; //Трек-номер
            $table->string('status')->nullable(); //Статус, текстом
            $table->integer('status_id')->nullable(); //Статус, числом
            $table->date('dt_send')->nullable(); //Дата доставки из службы доставки
            $table->integer('vesban')->nullable(); //Вес посылки
            $table->integer('ves_tarif')->nullable(); //
            $table->integer('str_tarif')->nullable(); //
            $table->string('comment', 255)->nullable(); //Комментарий
            $table->date('s_dt_send')->nullable(); //Дата 2
            $table->string('s_status')->nullable(); //Статус 2, текстом
            $table->integer('s_status_id_2')->nullable(); //Статус 2, числом 
            $table->string('postcode')->nullable(); //почтовый индекс
            $table->string('region', 255)->nullable(); //Регион
            $table->string('ops', 255)->nullable(); //Почтовое отделение
            $table->string('s_comment', 255)->nullable(); //Комментарии 2
            $table->string('tracking', 255)->nullable(); //
            $table->integer('is_work')->default('0'); //Вкл/выкл
            $table->integer('is_error')->default('0'); //Ошибка
            $table->integer('type')->default('0'); //Тип внутренний            
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
        Schema::dropIfExists('order_delivery_info');
    }
}
