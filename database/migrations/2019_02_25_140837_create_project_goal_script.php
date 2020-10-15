<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectGoalScript extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_goal_scripts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('project_goal_id')->unsigned()->nullable();
            $table->foreign('project_goal_id')->references('id')->on('project_goal')->onDelete('cascade');
            $table->string('name', 255); 
            $table->string('link', 255);              
            $table->boolean('status'); 
            $table->bigInteger('views');            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {        
        Schema::dropIfExists('project_goal_scripts');
    }
}
