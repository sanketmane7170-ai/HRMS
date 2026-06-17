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
        Schema::table('off_boardings', function (Blueprint $table) {
            if (!Schema::hasColumn('off_boardings', 'resignation_reason')) {
                $table->text('resignation_reason')->after('departure_reason_id')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('off_boardings', function (Blueprint $table) {
            $table->dropColumn('resignation_reason');
        });
    }
};
