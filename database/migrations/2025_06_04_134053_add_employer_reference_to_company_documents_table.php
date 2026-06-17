<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_documents', function (Blueprint $table) {
            $table->string('employer_reference')->nullable()->after('mol_code');
        });
    }

    public function down(): void
    {
        Schema::table('company_documents', function (Blueprint $table) {
            $table->dropColumn('employer_reference');
        });
    }
};
