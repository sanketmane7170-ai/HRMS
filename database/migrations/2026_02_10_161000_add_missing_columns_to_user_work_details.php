<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Added by Sanket to fix schema mismatch
     */
    public function up(): void
    {
        Schema::table('user_work_details', function (Blueprint $table) {
            if (!Schema::hasColumn('user_work_details', 'designation_id')) {
                $table->unsignedBigInteger('designation_id')->nullable()->after('user_id');
                $table->foreign('designation_id')->references('id')->on('designations')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('user_work_details', 'probation_month')) {
                $table->integer('probation_month')->nullable()->default(6)->after('probation_end_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_work_details', function (Blueprint $table) {
            if (Schema::hasColumn('user_work_details', 'designation_id')) {
                $table->dropForeign(['designation_id']);
                $table->dropColumn('designation_id');
            }
            
            if (Schema::hasColumn('user_work_details', 'probation_month')) {
                $table->dropColumn('probation_month');
            }
        });
    }
};
