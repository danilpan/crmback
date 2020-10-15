<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGeoIdToDeliveryTypeProjectTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('delivery_type_project', function (Blueprint $table) {
            $table->integer('geo_id')->unsigned()->nullable();
            $table->foreign('geo_id')->references('id')->on('geo')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('delivery_type_project', function (Blueprint $table) {
            $table->dropForeign('geo_id_foreign');
            $table->dropColumn('geo_id');
        });
    }
}
