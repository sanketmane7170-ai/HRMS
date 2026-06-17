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
        Schema::table('visa_processes', function (Blueprint $table) {
            // Stage 1: MOHRE
            $table->string('mohre_offer_file')->nullable();
            $table->string('labor_card_number')->nullable();
            
            // Stage 2: Entry
            $table->string('entry_permit_number')->nullable();
            $table->string('uid_number')->nullable();
            $table->string('visa_place_of_issue')->nullable(); // Dubai, Abu Dhabi, etc.

            // Stage 3: Medical & EID
            $table->string('medical_center_name')->nullable();
            $table->string('medical_type')->default('normal'); // normal, vip_24, vip_48
            $table->string('eid_application_form')->nullable();
            $table->date('eid_biometrics_date')->nullable();
            $table->string('eid_status')->default('pending'); // pending, typed, biometrics_done, card_printed, collected
            $table->string('eid_card_file')->nullable(); // Scan of ID

            // Stage 4: Residency
            $table->string('residency_file_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visa_processes', function (Blueprint $table) {
            //
        });
    }
};
