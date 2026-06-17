<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('employee_kpi_assignments', function (Blueprint $table) {
            $table->string('grade')->nullable();
            $table->string('remark')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('employee_kpi_assignments', function (Blueprint $table) {
            $table->dropColumn(['grade', 'remark']);
        });
    }
};
