<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersDialStepsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders_dial_steps', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('queue_id')->unsigned(); //- айди очереди в которой производился вызов (цифра)
            $table->integer('order_id')->unsigned(); //- айди заказа (цифра)
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->integer('dial_step')->unsigned(); //- шаг перезвона (цифра)
            $table->timestamp('dial_time'); // - Время когда должен совершиться звонок
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
        Schema::dropIfExists('orders_dial_steps');
    }
}
