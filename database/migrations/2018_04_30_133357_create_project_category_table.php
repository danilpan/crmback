<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_category', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255)->comment('Название категории');
            $table->integer('organization_id')->nullable()->comment('ID организации');
            $table->boolean('is_work')->default(0)->comment('Активен ли');
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
        Schema::dropIfExists('project_category');
    }
}
