<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductProjectTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Damir - Таблица многие-ко-многим, для продкута

        Schema::create('product_project', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('project_id')->comment('Id-проекта продукта');
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
        Schema::dropIfExists('product_project');
    }
}
