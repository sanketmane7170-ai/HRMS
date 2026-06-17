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
        Schema::table('user_emergency_contacts', function (Blueprint $table) {
            $table->string('local_person_name')->nullable()->after('emergency_name');
            $table->string('local_person_relation')->nullable()->after('emergency_relation');
            $table->string('local_person_phone')->nullable()->after('emergency_phone');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_emergency_contacts', function (Blueprint $table) {
            //
        });
    }
};
