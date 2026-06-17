<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ip_full_final_settlements', function (Blueprint $table) {
            // Itemized exit recoveries, previously only absorbable into the generic
            // other_deductions bucket.
            $table->decimal('asset_recovery', 12, 2)->default(0)->after('notice_pay_recovery');
            $table->decimal('loan_recovery', 12, 2)->default(0)->after('asset_recovery');
        });
    }

    public function down(): void
    {
        Schema::table('ip_full_final_settlements', function (Blueprint $table) {
            $table->dropColumn(['asset_recovery', 'loan_recovery']);
        });
    }
};
