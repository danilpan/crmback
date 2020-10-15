<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSmsRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms_rules', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('organization_id'); // ID-организации
            $table->integer('sms_provider_id'); // ID-смс провайдера, mobizon, smsc
            $table->foreign('sms_provider_id')->references('id')->on('sms_providers')->onDelete('cascade');
            $table->integer('geo_id'); // Id-гео: RU, KZ, KG и т.п
           // $table->string('name', 255); // Название правила
           //$table->text('comment', 255)->nullable(); // Комментарий
            $table->integer('type')->unsigned()->nullable();; // Тип правила, ручная-0 или автоматическая-1
            $table->integer('is_work')->default(1);
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
        Schema::dropIfExists('sms_rules');
    }
}
