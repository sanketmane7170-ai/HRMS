<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('appraisal_templates', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->after('name')->nullable;

            // If you have branches table
            
        });
    }

    public function down(): void
    {
        Schema::table('appraisal_templates', function (Blueprint $table) {
            $table->dropColumn('branch_id');
        });
    }
};
