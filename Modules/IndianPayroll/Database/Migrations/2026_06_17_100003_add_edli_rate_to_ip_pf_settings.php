<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ip_pf_settings', function (Blueprint $table) {
            // EDLI (A/c 21): employer-borne 0.5% of PF wage (capped at the PF wage
            // ceiling). EDLI admin charges (A/c 22) are abolished, so not stored.
            $table->decimal('edli_charges_rate', 5, 2)->default(0.50)->after('admin_charges_rate');
        });
    }

    public function down(): void
    {
        Schema::table('ip_pf_settings', function (Blueprint $table) {
            $table->dropColumn('edli_charges_rate');
        });
    }
};
