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

        Schema::table('interview_feedback', function (Blueprint $table) {
            if (! Schema::hasColumn('interview_feedback', 'interview_id')) { // Check if the column doesn't already exist
                $table->unsignedBigInteger('interview_id')->nullable()->after('application_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interview_feedback', function (Blueprint $table) {
            $table->dropColumn('interview_id');
        });
    }
};
