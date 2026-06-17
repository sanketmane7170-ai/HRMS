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
        Schema::table('company_documents', function (Blueprint $table) {
            $table->string('logo')->nullable()->after('status');
            $table->string('small_logo')->nullable()->after('logo');
            $table->string('sign')->nullable()->after('small_logo');
            $table->text('header')->nullable()->after('sign');
            $table->text('footer')->nullable()->after('header');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_documents', function (Blueprint $table) {
            $table->dropColumn([
                'logo',
                'small_logo',
                'sign',
                'header',
                'footer',
            ]);
        });
    }
};
