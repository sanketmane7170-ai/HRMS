<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_work_details', function (Blueprint $table) {
            if (! Schema::hasColumn('user_work_details', 'probation_month')) { // Check if the column doesn't already exist
                $table->unsignedInteger('probation_month')
                    ->nullable()
                    ->after('probation_end_date');
            }
        });

    }

    public function down(): void
    {
        Schema::table('user_work_details', function (Blueprint $table) {
            $table->dropColumn('probation_month');
        });
    }
};
