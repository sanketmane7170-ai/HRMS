<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recruitment_applications', function (Blueprint $table) {
            // Drop FK first
            $table->dropForeign(['user_id']);
        });

        Schema::table('recruitment_applications', function (Blueprint $table) {
            // Make nullable
            $table->unsignedBigInteger('user_id')->nullable()->change();

            // Re-add FK with SET NULL
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('recruitment_applications', function (Blueprint $table) {
            // Drop FK first
            $table->dropForeign(['user_id']);
        });

        Schema::table('recruitment_applications', function (Blueprint $table) {
            // Make NOT NULL
            $table->unsignedBigInteger('user_id')->nullable(false)->change();

            // Re-add FK WITHOUT SET NULL
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }
};

