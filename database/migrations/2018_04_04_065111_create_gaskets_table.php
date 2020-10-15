<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGasketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gaskets', function (Blueprint $table) {
            $table->integer('id')->nullable()->unique();
            $table->integer('id_organization')->nullable();
            $table->string('name', 100)->nullable();
            $table->string('url', 100)->nullable();
            $table->integer('id_product')->nullable();
            $table->decimal('cr', 10, 2)->nullable();
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
        Schema::dropIfExists('gaskets');
    }
}
