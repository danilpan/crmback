<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Damir - Таблица категорий продуктов

        Schema::create('product_category', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('organization_id')->nullable()->comment('Id-организации');
            $table->string('name', 255)->comment('Название категории');
            $table->integer('is_work')->default(0)->comment('Активен ли');
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
        Schema::dropIfExists('product_category');
    }
}
