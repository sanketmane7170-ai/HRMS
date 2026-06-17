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
        Schema::table('e_m_i_deductions', function (Blueprint $table) {
            $table->decimal('total_amount', 15, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('e_m_i_deductions', function (Blueprint $table) {
            $table->dropColumn('total_amount');
        });
    }
};
