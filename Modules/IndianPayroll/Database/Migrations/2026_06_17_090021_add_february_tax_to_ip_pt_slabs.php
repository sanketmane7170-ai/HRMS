<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ip_pt_slabs', function (Blueprint $table) {
            // Some states (notably Maharashtra) have a higher PT amount in February
            // than the standard monthly_tax.  NULL means no February-specific amount —
            // monthly_tax applies every month including February.
            $table->decimal('february_tax', 8, 2)->nullable()->after('monthly_tax');
        });
    }

    public function down(): void
    {
        Schema::table('ip_pt_slabs', function (Blueprint $table) {
            $table->dropColumn('february_tax');
        });
    }
};
