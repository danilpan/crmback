<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Damir - Таблица многие-ко-многим, для продкута

        Schema::create('products_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('category_id')->comment('Id-категории продуктов');
            $table->integer('product_id')->comment('Id-продукта');
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
        Schema::dropIfExists('products_categories');
    }
}
