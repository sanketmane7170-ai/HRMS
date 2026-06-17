<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('designations', function (Blueprint $table) {
            // Drop old unique index if exists
            $table->dropUnique('designations_code_unique');

            // Add composite unique indexes
            $table->unique(['department_id', 'code'], 'unique_department_code');
            $table->unique(['department_id', 'name'], 'unique_department_name');
        });
    }

    public function down(): void
    {
        Schema::table('designations', function (Blueprint $table) {
            // Drop composite unique indexes
            $table->dropUnique('unique_department_code');
            $table->dropUnique('unique_department_name');

            // Optional: add back old unique if needed
            $table->unique('code', 'designations_code_unique');
        });
    }
};
