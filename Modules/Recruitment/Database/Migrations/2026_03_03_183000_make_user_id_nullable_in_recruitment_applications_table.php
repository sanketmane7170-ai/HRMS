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
        Schema::table('recruitment_applications', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            
            // Re-evaluating the unique constraint if it exists
            // The original migration had: $table->unique(['job_id', 'user_id']);
            // If user_id is now nullable, we might want to drop the unique constraint or keep it as is
            // since multiple (job_id, null) entries are usually allowed in MySQL but depend on requirements.
            // However, we already have duplicate checks in the controller based on email.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recruitment_applications', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }
};
