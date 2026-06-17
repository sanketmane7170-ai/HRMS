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
        Schema::table('e_m_i_allowances', function (Blueprint $table) {
            $table->text('remark')->nullable()->after('fully_paid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('e_m_i_allowances', function (Blueprint $table) {
            $table->dropColumn('remark');
        });
    }
};
