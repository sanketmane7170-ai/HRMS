<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Add the new JSON column if it doesn't exist
        if (!Schema::hasColumn('user_work_details', 'report_to_ids')) {
            Schema::table('user_work_details', function (Blueprint $table) {
                $table->json('report_to_ids')->nullable()->after('shift_end');
            });
        }

        // Step 2: Migrate old int data to JSON format like [2]
        // checks if report_to_id exists
        if (Schema::hasColumn('user_work_details', 'report_to_id')) {
            DB::table('user_work_details')
                ->whereNotNull('report_to_id')
                ->oldest('id')
                ->chunk(100, function ($rows) {
                    foreach ($rows as $row) {
                        DB::table('user_work_details')
                            ->where('id', $row->id)
                            ->update(['report_to_ids' => json_encode([(int) $row->report_to_id])]); // Ensure it is an integer
                    }
                });
            
             // Step 3: Drop the old column
            Schema::table('user_work_details', function (Blueprint $table) {
                $table->dropColumn('report_to_id');
            });
        }
        
        // Handle case where rename might have partially worked or "old_report_to_id" exists from manual intervention
        if (Schema::hasColumn('user_work_details', 'old_report_to_id')) {
             // Migrate data from old_report_to_id if report_to_ids is empty
             // This is a safety check
             DB::table('user_work_details')
                ->whereNotNull('old_report_to_id')
                ->whereNull('report_to_ids')
                ->oldest('id')
                ->chunk(100, function ($rows) {
                    foreach ($rows as $row) {
                         DB::table('user_work_details')
                            ->where('id', $row->id)
                            ->update(['report_to_ids' => json_encode([(int) $row->old_report_to_id])]);
                    }
                });

            Schema::table('user_work_details', function (Blueprint $table) {
                $table->dropColumn('old_report_to_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('user_work_details', 'report_to_id')) {
             Schema::table('user_work_details', function (Blueprint $table) {
                $table->integer('report_to_id')->nullable()->after('shift_end');
            });
        }

        // Restore data (take first id from array)
        if (Schema::hasColumn('user_work_details', 'report_to_ids')) {
             DB::table('user_work_details')->whereNotNull('report_to_ids')->chunk(100, function ($rows) {
                foreach ($rows as $row) {
                    $ids = json_decode($row->report_to_ids, true);
                    if (!empty($ids) && is_array($ids)) {
                        DB::table('user_work_details')
                            ->where('id', $row->id)
                            ->update(['report_to_id' => $ids[0]]); // Take first ID
                    }
                }
            });

            Schema::table('user_work_details', function (Blueprint $table) {
                $table->dropColumn('report_to_ids');
            });
        }
    }
};
