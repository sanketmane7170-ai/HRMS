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
            $table->boolean('medical_insurance_provided')->default(false)->after('report_to_id');
            $table->decimal('annual_premium', 10, 2)->nullable()->after('medical_insurance_provided');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_work_details', function (Blueprint $table) {
            $table->dropColumn(['medical_insurance_provided', 'annual_premium']);
        });
    }
};
