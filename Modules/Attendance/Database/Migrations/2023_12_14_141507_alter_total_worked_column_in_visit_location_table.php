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
            $table->double('total_worked')->after('visit_out')->default(0)->comment('in minutes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('location_visits', function (Blueprint $table) {
            $table->dropColumn('total_worked');
        });
    }
};
