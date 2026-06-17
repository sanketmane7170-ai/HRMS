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
        Schema::table('recruitment_interviews', function (Blueprint $table) {
            $table->unsignedBigInteger('interviewer_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recruitment_interviews', function (Blueprint $table) {
            // We cannot easily revert to not null if there are null values, 
            // but for strict reversibility we can try.
            // In practice, we might leave it nullable or require data cleanup.
            // $table->unsignedBigInteger('interviewer_id')->nullable(false)->change();
        });
    }
};
