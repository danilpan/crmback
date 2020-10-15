<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code_product')->nullable();
            $table->string('uniqued_import_id')->nullable();
            $table->integer('organization_id')->nullable();
            $table->integer('category_id')->default(1)->comment('Категория продуктов, таблица: product_categories');
            $table->string('article',255);
            $table->json('img')->nullable();
            $table->string('name',255);
            $table->integer('price_cost');
            $table->integer('price_online')->nullable();
            $table->integer('price_prime')->nullable();
            $table->string('weight',255);
            $table->text('desc');
            $table->string('script',255)->nullable();
            $table->string('basic_unit',15)->nullable();
            $table->integer('nabor')->nullable();
            $table->integer('service')->nullable();
            $table->integer('complect')->nullable();
            $table->string('basic_unit_seat',15)->nullable();
            $table->boolean('is_work')->nullable()->default(true);
            $table->boolean('is_kit')->default(false);
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
        Schema::dropIfExists('products');
    }
}
