<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectPagePhoneTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_page_phone', function (Blueprint $table) {
            $table->increments('id');
	    $table->integer('project_page_id')->unsigned()->nullable();
	    $table->foreign('project_page_id')->references('id')->on('project_page')->onDelete('cascade');
	    $table->text('phone');
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
        Schema::dropIfExists('project_page_phone');
    }
}
