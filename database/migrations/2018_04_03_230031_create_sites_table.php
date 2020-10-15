<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('phone_id')->nullable();
            $table->integer('organization_id')->unsigned()->nullable();
            $table->integer('project_id')->unsigned()->nullable();
            $table->string('url')->nullable();
            $table->boolean('active')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();


//            $table->string('import_id', 60)->nullable();
//            $table->integer('id_organization')->nullable();
//            $table->string('title', 255)->nullable();
//            $table->string('url', 255)->nullable();
//            $table->string('desc', 255)->nullable();
//            $table->integer('is_work')->nullable();
//            $table->string('project_id')->nullable();
//            $table->integer('products_id')->nullable();
//            $table->integer('phone')->nullable();
//            $table->integer('create_user')->nullable();
//            $table->integer('create_date')->nullable();
//            $table->integer('update_date')->nullable();
//            $table->integer('update_user')->nullable();

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
        Schema::dropIfExists('sites');
    }
}
