<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ip_payroll_runs', function (Blueprint $table) {
            // Stores the exception message when a queued compute job fails,
            // so HR can see why without digging into the Laravel log.
            $table->text('compute_error')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('ip_payroll_runs', function (Blueprint $table) {
            $table->dropColumn('compute_error');
        });
    }
};
