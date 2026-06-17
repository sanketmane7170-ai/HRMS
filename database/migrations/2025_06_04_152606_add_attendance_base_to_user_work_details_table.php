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
            $table->string('attendance_base')->default('yes')->after('salary_mode');
            $table->string('company_accommodation')->nullable()->after('attendance_base');
            $table->string('accommodation_location')->nullable()->after('company_accommodation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_work_details', function (Blueprint $table) {
            //
        });
    }
};
