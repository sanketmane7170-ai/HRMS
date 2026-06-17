<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Added by Sanket to fix missing foreign key constraint
     */
    public function up(): void
    {
        // Check if foreign key already exists
        $fkExists = DB::select(
            "SELECT CONSTRAINT_NAME 
             FROM information_schema.KEY_COLUMN_USAGE 
             WHERE TABLE_NAME = 'recruitment_offers' 
             AND COLUMN_NAME = 'created_by'
             AND CONSTRAINT_SCHEMA = DATABASE() 
             AND REFERENCED_TABLE_NAME = 'users'"
        );

        if (empty($fkExists) && Schema::hasColumn('recruitment_offers', 'created_by')) {
            Schema::table('recruitment_offers', function (Blueprint $table) {
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recruitment_offers', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
        });
    }
};
