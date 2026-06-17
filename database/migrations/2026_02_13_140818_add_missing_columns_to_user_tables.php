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
            $table->string('mol_location')->nullable()->after('accommodation_location');
            $table->string('insurance_number')->nullable()->after('medical_insurance_provided');
            $table->date('insurance_expiry')->nullable()->after('insurance_number');
            $table->date('last_working_day')->nullable()->after('probation_end_date');
            $table->text('remarks')->nullable()->after('document_request_charge');
        });

        Schema::table('user_profiles', function (Blueprint $table) {
            $table->string('visa_designation')->nullable()->after('visa_category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_work_details', function (Blueprint $table) {
            $table->dropColumn(['mol_location', 'insurance_number', 'insurance_expiry', 'last_working_day', 'remarks']);
        });

        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn('visa_designation');
        });
    }
};
