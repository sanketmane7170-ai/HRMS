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
        Schema::table('user_salary_allowances', function (Blueprint $table) {
            $table->boolean('is_fixed_for_current_month')->after('year')->default(0);
        });

        Schema::table('user_deductions', function (Blueprint $table) {
            $table->boolean('is_fixed_for_current_month')->after('year')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_salary_allowances', function (Blueprint $table) {
            $table->dropColumn('is_fixed_for_current_month');
        });

        Schema::table('user_deductions', function (Blueprint $table) {
            $table->dropColumn('is_fixed_for_current_month');
        });
    }
};
