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
        $table = 'interview_feedback';
        $foreignKey = 'interview_feedback_interview_id_foreign';

        // Check if the foreign key exists before trying to drop or add it
        $foreignKeys = DB::select(
            "SELECT CONSTRAINT_NAME 
             FROM information_schema.KEY_COLUMN_USAGE 
             WHERE TABLE_NAME = ? 
             AND CONSTRAINT_SCHEMA = DATABASE() 
             AND CONSTRAINT_NAME = ?", 
            [$table, $foreignKey]
        );

        if (!empty($foreignKeys)) {
            Schema::table($table, function (Blueprint $table) use ($foreignKey) {
                $table->dropForeign($foreignKey);
            });
        }

        Schema::table($table, function (Blueprint $table) {
            // Add the correct foreign key pointing to 'recruitment_interviews'
            // Added by Sanket
            $table->foreign('interview_id')
                  ->references('id')
                  ->on('recruitment_interviews')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interview_feedback', function (Blueprint $table) {
            $table->dropForeign(['interview_id']);
        });
    }
};
