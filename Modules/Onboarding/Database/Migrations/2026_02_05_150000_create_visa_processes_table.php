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
        if (Schema::hasTable('visa_processes')) {
            return;
        }
        Schema::create('visa_processes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('mohre_contract_status')->default('pending');
            $table->string('mohre_contract_file')->nullable();
            $table->string('work_permit_status')->default('pending');
            $table->string('work_permit_file')->nullable();
            $table->string('entry_permit_status')->default('pending');
            $table->string('entry_permit_file')->nullable();
            $table->boolean('status_change_completed')->default(false);
            $table->string('medical_status')->default('pending');
            $table->date('medical_appointment_date')->nullable();
            $table->string('medical_result_file')->nullable();
            $table->string('insurance_status')->default('pending');
            $table->string('insurance_card_file')->nullable();
            $table->string('residency_visa_status')->default('pending');
            $table->string('residency_visa_file')->nullable();
            $table->date('visa_expiry_date')->nullable();
            $table->string('mohre_offer_file')->nullable();
            $table->string('labor_card_number')->nullable();
            $table->string('entry_permit_number')->nullable();
            $table->string('uid_number')->nullable();
            $table->string('visa_place_of_issue')->nullable();
            $table->string('medical_center_name')->nullable();
            $table->string('medical_type')->nullable();
            $table->string('eid_application_form')->nullable();
            $table->date('eid_biometrics_date')->nullable();
            $table->string('eid_status')->nullable();
            $table->string('eid_card_file')->nullable();
            $table->string('residency_file_number')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visa_processes');
    }
};
