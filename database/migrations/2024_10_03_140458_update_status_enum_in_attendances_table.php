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
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('present', 'absent', 'leave', 'holiday', 'weekend', 'late') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->enum('status', ['present', 'absent', 'leave', 'holiday', 'weekend'])
                  ->charset('utf8mb4')
                  ->collation('utf8mb4_unicode_ci')
                  ->change();
        });
    }
};
