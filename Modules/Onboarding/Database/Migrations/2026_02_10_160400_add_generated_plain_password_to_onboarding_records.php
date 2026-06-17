<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Added by Sanket to fix production error where column is referenced but doesn't exist
     */
    public function up(): void
    {
        if (!Schema::hasColumn('onboarding_records', 'generated_plain_password')) {
            Schema::table('onboarding_records', function (Blueprint $table) {
                $table->string('generated_plain_password')->nullable()->after('email');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('onboarding_records', 'generated_plain_password')) {
            Schema::table('onboarding_records', function (Blueprint $table) {
                $table->dropColumn('generated_plain_password');
            });
        }
    }
};
