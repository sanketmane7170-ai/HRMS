<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ip_employee_profiles', function (Blueprint $table) {
            $table->enum('employment_type', ['permanent', 'contract', 'intern', 'consultant'])
                ->default('permanent')
                ->after('date_of_joining');
        });
    }

    public function down(): void
    {
        Schema::table('ip_employee_profiles', function (Blueprint $table) {
            $table->dropColumn('employment_type');
        });
    }
};
