<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $default_allowances = json_encode([
            "housing_allowance" => 0,
            "transportation_allowance" => 0,
            "other_allowance" => 0,
            "tips" => 0
        ]);
        $default_deductions = json_encode([
            "advance_salary" => 0,
            "loan_deduction" => 0,
            "other_deduction" => 0
        ]);
        Schema::table('user_salaries', function (Blueprint $table){
            $table->text('fixed_allowances')->nullable(true);
            $table->text('fixed_deductions')->nullable(true);
        });

        DB::table('user_salaries')->update([
            'fixed_allowances' => $default_allowances,
            'fixed_deductions' => $default_deductions,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_salaries', function (Blueprint $table) {
            $table->dropColumn('fixed_allowances');
            $table->dropColumn('fixed_deductions');
        });
    }
};
