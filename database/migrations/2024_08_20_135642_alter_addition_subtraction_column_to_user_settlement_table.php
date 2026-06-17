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
        Schema::table('user_settlements', function (Blueprint $table) {
            $table->decimal('total_additions',10,2)->after('settlement_amount');
            $table->decimal('total_deductions',10,2)->after('total_additions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_settlements', function (Blueprint $table) {
            $table->dropColumn('total_deductions');
            $table->dropColumn('total_additions');
        });
    }
};
