<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('location_visits', function (Blueprint $table) {
            $table->string('longitude')->after('visit_out')->nullable(true);
            $table->string('latitude')->after('longitude')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('location_visits', function (Blueprint $table){
            $table->dropColumn('longitude');
            $table->dropColumn('latitude');
        });
    }
};
