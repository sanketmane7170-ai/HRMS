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
        Schema::table('user_warnings', function (Blueprint $table) {
            DB::statement("ALTER TABLE user_warnings MODIFY COLUMN type ENUM('verbal', 'first', 'second', 'third', 'performance', 'notice_of_termination', 'attendance_issue', 'termination') NOT NULL");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_warnings', function (Blueprint $table) {
            DB::statement("ALTER TABLE user_warnings MODIFY COLUMN type ENUM('verbal','first','second','third','performance','notice_of_termination','attendance_issue') NOT NULL");
        });
    }
};
