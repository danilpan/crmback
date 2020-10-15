<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('login')->nullable()->unique();
            $table->string('mail', 50)->nullable()->unique();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('phone_office')->nullable();
            $table->string('password')->nullable();
            $table->boolean('is_work')->nullable()->default(true);
            $table->integer('is_show_work')->default(1)->nullable();
            $table->boolean('out_calls')->default(true);
            $table->string('ip')->nullable();
            $table->timestamp('last_online')->nullable();
            $table->string('speaker_status', 100)->nullable();
            $table->integer('organization_id')->nullable()->index();
            $table->integer('company_id')->nullable()->index();
            $table->string('pseudo_session')->nullable();
            $table->string('mainlink')->nullable()->default(0);
            $table->timestamps();

//            $table->integer('last_online')->nullable();
//            $table->date('last_online_date')->nullable();
//            $table->string('check_summ')->nullable();
//            $table->integer('option_cache')->default(1);
//            $table->integer('option_in_call')->default(1);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
