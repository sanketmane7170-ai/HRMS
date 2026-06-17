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
        Schema::table('recruitment_application_logs', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['changed_by']);
            
            // Make changed_by nullable
            $table->unsignedBigInteger('changed_by')->nullable()->change();
            
            // Re-add the foreign key constraint with SET NULL on delete
            $table->foreign('changed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recruitment_application_logs', function (Blueprint $table) {
            // Drop the nullable foreign key
            $table->dropForeign(['changed_by']);
            
            // Make changed_by non-nullable again
            $table->unsignedBigInteger('changed_by')->nullable(false)->change();
            
            // Re-add the original foreign key constraint
            $table->foreign('changed_by')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
