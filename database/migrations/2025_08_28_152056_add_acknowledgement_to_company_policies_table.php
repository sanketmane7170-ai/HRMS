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
        Schema::table('company_policies', function (Blueprint $table) {
            $table->text('acknowledgement')->nullable()->after('document');
            // use ->after('some_column') to place it at the right spot
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_policies', function (Blueprint $table) {
            $table->dropColumn('acknowledgement');
        });
    }
};
