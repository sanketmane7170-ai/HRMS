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
        Schema::dropIfExists('recruitment_interviews');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We don't need to recreate the old table as we have the new interviews table
    }
};
