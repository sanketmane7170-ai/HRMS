<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_work_details', function (Blueprint $table) {
            $table->time('shift_start')->after('user_id')->nullable();
            $table->time('shift_end')->after('shift_start')->nullable();
            $table->integer('report_to_id')->after('shift_end')->nullable();
            //$table->string('report_to_name')->after('report_to_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_work_details', function (Blueprint $table) {
            $table->dropColumn('shift_start');
            $table->dropColumn('shift_end');
            $table->dropColumn('report_to_id');
            //$table->dropColumn('report_to_name');
        });
    }
};
