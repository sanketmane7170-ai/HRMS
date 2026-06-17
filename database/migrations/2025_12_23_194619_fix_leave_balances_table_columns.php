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
        Schema::table('leave_balances', function (Blueprint $table) {
            if (!Schema::hasColumn('leave_balances', 'isAddThisMonthLeave')) {
                $table->string('isAddThisMonthLeave')->nullable()->after('leave_type_id');
            }
            if (!Schema::hasColumn('leave_balances', 'thisYearAvailableLeave')) {
                $table->integer('thisYearAvailableLeave')->nullable()->after('leave_type_id');
            }
            if (!Schema::hasColumn('leave_balances', 'is_add_ph_leave')) {
                $table->boolean('is_add_ph_leave')->default(false)->after('leave_type_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_balances', function (Blueprint $table) {
            $table->dropColumn(['isAddThisMonthLeave', 'thisYearAvailableLeave', 'is_add_ph_leave']);
        });
    }
};
