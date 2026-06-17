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
        Schema::table('users_shifts', function (Blueprint $table) {
            if (!Schema::hasColumn('users_shifts', 'created_by')) {
                $table->integer('created_by')->nullable(); // Add the new column if it doesn't exist
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users_shifts', function (Blueprint $table) {
            if (Schema::hasColumn('users_shifts', 'created_by')) {
                $table->dropColumn('created_by'); // Rollback the column if it exists
            }
        });
    }
};
