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
        Schema::table('recruitment_offers', function (Blueprint $table) {
            if (!Schema::hasColumn('recruitment_offers', 'position')) {
                $table->string('position')->nullable()->after('application_id');
            }
            if (!Schema::hasColumn('recruitment_offers', 'department')) {
                $table->string('department')->nullable()->after('position');
            }
            if (!Schema::hasColumn('recruitment_offers', 'salary_currency')) {
                $table->string('salary_currency', 10)->nullable()->after('salary');
            }
            if (!Schema::hasColumn('recruitment_offers', 'salary_period')) {
                $table->string('salary_period', 20)->nullable()->after('salary_currency');
            }
            if (!Schema::hasColumn('recruitment_offers', 'salary_type')) {
                $table->string('salary_type', 20)->nullable()->after('salary_period');
            }
            if (!Schema::hasColumn('recruitment_offers', 'additional_notes')) {
                $table->text('additional_notes')->nullable()->after('notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recruitment_offers', function (Blueprint $table) {
            $table->dropColumn([
                'position',
                'department',
                'salary_currency',
                'salary_period',
                'salary_type',
                'additional_notes'
            ]);
        });
    }
};
