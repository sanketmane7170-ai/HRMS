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
        Schema::table('leave_balance_update_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('leave_balance_update_logs', 'diff_value')) {
                $table->string('diff_value')->nullable();
            }
            if (!Schema::hasColumn('leave_balance_update_logs', 'is_less')) {
                $table->integer('is_less')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_balance_update_logs', function (Blueprint $table) {
            $table->dropColumn('diff_value');
            $table->dropColumn('is_less');
        });
    }
};
