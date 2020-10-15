<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->increments('id')->unique();
            $table->string('uniqued_import_id')->nullable()->comment('для 1С');

            $table->string('product_code')->nullable()->comment('для 1С');

            $table->integer('order_id');
            $table->integer('product_id');

            $table->string('comment')->nullable()->comment('Комментарий к товару');
            $table->string('name')->nullable()->comment('Имя продажи на момент добавления');

            $table->integer('product_price')->nullable()->comment('стоимость продукта по умолчанию');
            $table->integer('price')->nullable()->comment('Цена в карточке заказа');
            $table->integer('prime_price')->nullable()->comment('Себестоимость (берем из таблицы продуктс)');

            $table->integer('cost_price')->nullable()->comment('Стоимость ?');
            $table->integer('is_cart')->default(0)->comment('товар добавленный в заказ системой автоматически');

            $table->integer('upsale')->comment('Доп продажа ( 0 - нет, 1 - первого уровня, 2 - второго уровня)');

            $table->integer('upsale_user_id')->default(0)->comment('Оператор который поставил доп продажу');
            $table->integer('user_id')->default(0)->comment('Оператор который добавил товар');

            $table->integer('lead_id')->default(0)->comment('ID лида');

            $table->string('weight')->default(0)->comment('Вес посылки');
            $table->string('article')->default(0)->comment('Артикул');

            $table->integer('quantity')->default(0)->comment('Количество товара');
            $table->integer('quantity_price')->default(0)->comment('Стоимость price*quantity');
            $table->integer('quantity_pay')->default(0)->comment('Стоимость ?');
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
        Schema::dropIfExists('sales');
    }
}
