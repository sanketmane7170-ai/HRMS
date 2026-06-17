<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('user_deductions', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')
                  ->nullable()
                  ->after('user_id'); // change position if needed

        });
    }

    public function down(): void
    {
        Schema::table('user_deductions', function (Blueprint $table) {
            
            $table->dropColumn('branch_id');
        });
    }
};
