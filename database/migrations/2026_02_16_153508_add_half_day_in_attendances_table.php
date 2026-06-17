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
        Schema::table('attendances', function (Blueprint $table) {
            DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('present','absent','leave','holiday','weekend','late','sickleave','earlyout','halfday') COLLATE utf8mb4_unicode_ci NOT NULL");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('present','absent','leave','holiday','weekend','late','sickleave','earlyout') COLLATE utf8mb4_unicode_ci NOT NULL");
        });
    }
};
