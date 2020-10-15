<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectGoalScriptProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_goal_script_products', function (Blueprint $table) {
            $table->integer('project_goal_script_id')->unsigned();
            $table->foreign('project_goal_script_id')->references('id')->on('project_goal_scripts')->onDelete('cascade');
            $table->integer('product_id')->unsigned();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');            
            $table->text('note')->nullable();
            $table->integer('price');
            $table->integer('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_goal_script_products');
    }
}
