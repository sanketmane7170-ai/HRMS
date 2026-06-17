<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE user_work_details MODIFY COLUMN is_rider TINYINT NULL DEFAULT NULL");
    }

    public function down(): void
    {
        Schema::table('user_work_details', function (Blueprint $table) {
            $table->tinyInteger('is_rider')->default(0)->nullable(false)->change();
        });
    }
};