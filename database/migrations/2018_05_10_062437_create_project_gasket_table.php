<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectGasketTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_gasket', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('organization_id');
            
			$table->integer('project_id')->unsigned()->nullable();
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
           
            $table->string('name', 255); 
            $table->string('link', 255); 
            $table->string('import_id')->nullable();            // ID импорта
            $table->boolean('private')->default(false);         // Приватная
 
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
        Schema::dropIfExists('project_gasket');
    }
}
