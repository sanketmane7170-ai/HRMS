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
            $table->date('date')->after('salary_id')->nullable(true);
            $table->integer('month_code')->after('date')->comment("1 means january and 12 means december")->nullable(true);
            $table->integer('year')->after('month_code')->comment("check in which year we want allowance details")->nullable(true);
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
            $table->dropColumn('date');
            $table->dropColumn('month_code');
            $table->dropColumn('year');
        });
    }
};
