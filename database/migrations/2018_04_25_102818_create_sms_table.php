<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::create('sms', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('organization_id'); //ID организации
            $table->integer('order_id'); // ID заказа
            $table->integer('user_id'); // ID пользователя
            $table->integer('sms_provider_id'); // SmsProvider
            $table->string('phone', 255); //Номер телефона
            $table->text('message', 550); //Текст сообщения
            $table->string('status', 255); //Статус crm
            $table->string('sms_provider_status', 255)->nullable();//Статус сервиса
            $table->string('service_id', 255); //Внутрений ID сервиса            
            $table->integer('type'); // Тип отправки
            $table->float('price')->nullable(); // Тип отправки
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
        Schema::dropIfExists('sms');
    }
}
