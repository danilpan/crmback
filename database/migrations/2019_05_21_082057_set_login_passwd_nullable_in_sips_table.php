<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SetLoginPasswdNullableInSipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sips', function (Blueprint $table) {
            $table->string('passwd', 80)->nullable()->change();
            $table->string('login', 80)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sips', function (Blueprint $table) {
            $table->string('passwd', 80)->change();
            $table->string('login', 80)->change();
        });
    }
}
