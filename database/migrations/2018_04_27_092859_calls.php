<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Calls extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calls', function (Blueprint $table) {
            $table->string('id')->primary();

            $table->integer('organization_id')->nullable()->comment('id организации');
            $table->integer('queue_id')->nullable()->comment('id очереди');
            $table->integer('step_id')->nullable()->comment('шаг набора');
            $table->integer('order_id')->nullable()->comment('ID заказа');
            $table->integer('rule_id')->nullable()->comment('ID маршрута');

            $table->integer('weight')->nullable()->comment('Вес звонка');
            $table->string('call_type')->nullable()->comment('Тип звонка');

            $table->integer('sip')->nullable()->comment('Номер менеджера');
            $table->string('phone')->comment('Номер клиента');
            $table->string('dst')->nullable()->comment('Номер на АТС');

            $table->string('record_link')->nullable()->comment('Имя файла с записью');
            $table->integer('record_time')->nullable()->comment('Длина записи разговора');

            $table->timestamp('time')->nullable()->comment('Время начала набора');
            $table->integer('billing_time')->nullable()->comment('Время с начала соединения с клиентом');
            $table->integer('duration_time')->nullable()->comment('Общяя длитильность звонка');

            $table->string('disposition')->nullable()->comment('Результат набора');

            // $table->integer('addmember')->default(0)->comment('Добавлен канал');
            // $table->integer('agentdump')->default(0)->comment('Агент сбросил звонящего во время прослушивания приглашения очереди.');
            // $table->integer('agentlogin')->default(0)->comment('Агент залогинился. Канал записан.');
            // $table->integer('agentlogoff')->default(0)->comment('Оператор разлогинелся');
            // $table->integer('completeagent')->default(0)->comment('Завершен оператором');
            // $table->integer('completecaller')->default(0)->comment('Завершен клиентом');
            // $table->integer('configreload')->default(0)->comment('Перезагрузка конфигурации');
            // $table->integer('connect')->default(0)->comment('Клиент и оператор оба в разговоре');
            // $table->integer('enterqueue')->default(0)->comment('Звонок пришел в очередь');
            // $table->integer('exitempty')->default(0)->comment('Абонент вышел из очереди потому что в очереди не было доступных агентов, если это настроено в конфиге');
            // $table->integer('caexitwithkeyll')->default(0)->comment('Результат набора');
            // $table->integer('exitwithtimeout')->default(0)->comment('Абонент был выброшен из очереди по таймауту(очень долго ждал). записывается позиция выхода, первоначальная позиция и время ожидания');
            // $table->integer('ringnoanswer')->default(0)->comment('После попытки дозвониться доступному агенту(в мс), агент не взял трубочку');
            // $table->integer('abandon')->default(0)->comment('Пользователь устал ждать в очереди оператора и ушёл');

            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('calls');
    }
}
