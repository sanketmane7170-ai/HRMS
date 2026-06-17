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
        Schema::table('expenses', function (Blueprint $table) {
            // Add new columns for HR and Line Manager approvals
            $table->foreignId('hr_id')->nullable()->constrained('users')->onDelete('set null')->after('status');
            $table->enum('hr_status', ['pending', 'approved', 'rejected'])->default('pending')->after('hr_id');
            $table->text('hr_comments')->nullable()->after('hr_status');
            
            $table->foreignId('lm_id')->nullable()->constrained('users')->onDelete('set null')->after('hr_comments');
            $table->enum('lm_status', ['pending', 'approved', 'rejected'])->default('pending')->after('lm_id');
            $table->text('lm_comments')->nullable()->after('lm_status');

            

            // Remove the document column
            $table->dropColumn('document');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            // Drop the new columns in the rollback
            $table->dropForeign(['hr_id']);
            $table->dropColumn(['hr_id', 'hr_status', 'hr_comments']);
            
            $table->dropForeign(['lm_id']);
            $table->dropColumn(['lm_id', 'lm_status', 'lm_comments']);

            // Restore the document column
            $table->string('document')->nullable();
        });
    }
};
