<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLnkRoleOrganizationProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lnk_role__organization_projects', function (Blueprint $table) {
            $table->increments('id');
            
            $table->integer('role_id')->comment('Id-роли');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');

            $table->integer('organization_id')->nullable();
            $table->integer('project_id')->nullable();

            $table->boolean('is_deduct_organization')->nullable()->comment('За вычетом организаций');
            $table->boolean('is_deduct_project')->nullable()->comment('За вычетом проектов');

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
        Schema::dropIfExists('lnk_role__organization_projects');
    }
}
