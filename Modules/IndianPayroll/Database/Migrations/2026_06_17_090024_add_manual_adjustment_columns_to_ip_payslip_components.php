<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ip_payslip_components', function (Blueprint $table) {
            // Allow HR-entered ad-hoc deductions that have no catalog component
            $table->foreignId('salary_component_id')->nullable()->change();

            // Human-readable label for manual entries (used when salary_component_id is null)
            $table->string('label', 150)->nullable()->after('salary_component_id');

            // Distinguishes engine-computed rows from HR-added adjustments
            $table->boolean('is_manual')->default(false)->after('label');
        });
    }

    public function down(): void
    {
        Schema::table('ip_payslip_components', function (Blueprint $table) {
            $table->dropColumn(['label', 'is_manual']);
            $table->foreignId('salary_component_id')->nullable(false)->change();
        });
    }
};
