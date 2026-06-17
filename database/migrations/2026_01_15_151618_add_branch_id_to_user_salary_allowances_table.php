<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('user_salary_allowances', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')
                  ->nullable()
                  ->after('user_id'); // adjust position if needed

            // Optional: add foreign key if branches table exists
            // $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('user_salary_allowances', function (Blueprint $table) {
            // Optional: drop FK first if added
            // $table->dropForeign(['branch_id']);

            $table->dropColumn('branch_id');
        });
    }
};
