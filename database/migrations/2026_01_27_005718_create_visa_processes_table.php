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
        Schema::create('visa_processes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            
            // Phase 2.1: MOHRE Contract
            $table->string('mohre_contract_status')->default('pending'); // pending, drafted, signed
            $table->string('mohre_contract_file')->nullable();
            
            // Phase 2.2: Work Permit
            $table->string('work_permit_status')->default('pending'); // pending, applied, rejected, approved
            $table->string('work_permit_file')->nullable();
            
            // Phase 2.3: Entry Permit (Pink Visa)
            $table->string('entry_permit_status')->default('pending'); // pending, issued
            $table->string('entry_permit_file')->nullable();
            
            // Phase 2.4: Status Change
            $table->boolean('status_change_completed')->default(false);
            
            // Phase 2.5: Medical & Biometrics
            $table->string('medical_status')->default('pending'); // pending, scheduled, fit, unfit
            $table->date('medical_appointment_date')->nullable();
            $table->string('medical_result_file')->nullable();
            
            // Phase 2.6: Insurance & Residency
            $table->string('insurance_status')->default('pending'); // pending, active
            $table->string('insurance_card_file')->nullable();
            
            $table->string('residency_visa_status')->default('pending'); // pending, stamped
            $table->string('residency_visa_file')->nullable();
            $table->date('visa_expiry_date')->nullable();
            
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
