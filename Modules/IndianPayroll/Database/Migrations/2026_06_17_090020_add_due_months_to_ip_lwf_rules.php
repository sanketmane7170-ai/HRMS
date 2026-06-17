<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ip_lwf_rules', function (Blueprint $table) {
            // JSON array of calendar months (1–12) in which this state's LWF falls due.
            // NULL means the rule's frequency column already encodes "monthly", so every
            // month is a due month.  Non-null examples: [6, 12] for half-yearly Jun/Dec,
            // [12] for annual-December states.
            $table->json('due_months')->nullable()->after('frequency');
        });
    }

    public function down(): void
    {
        Schema::table('ip_lwf_rules', function (Blueprint $table) {
            $table->dropColumn('due_months');
        });
    }
};
