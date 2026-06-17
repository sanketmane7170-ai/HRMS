<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->unsignedBigInteger('check_in_branch_id')
                  ->nullable()
                  ->after('user_id');

            $table->unsignedBigInteger('check_out_branch_id')
                  ->nullable()
                  ->after('check_in_branch_id');

          
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
           
            $table->dropColumn([
                'check_in_branch_id',
                'check_out_branch_id',
            ]);
        });
    }
};
