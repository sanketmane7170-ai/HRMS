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
        Schema::table('recruitment_applications', function (Blueprint $table) {
            $table->string('portfolio_url')->nullable()->after('linkedin_url');
            $table->integer('years_experience')->nullable()->after('availability_date');
            $table->string('current_company')->nullable()->after('years_experience');
            $table->string('current_position')->nullable()->after('current_company');
            $table->integer('notice_period')->nullable()->after('current_position');
            $table->boolean('willing_to_relocate')->default(false)->after('notice_period');
            $table->boolean('authorization_to_work')->default(false)->after('willing_to_relocate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recruitment_applications', function (Blueprint $table) {
            $table->dropColumn([
                'portfolio_url',
                'years_experience',
                'current_company',
                'current_position',
                'notice_period',
                'willing_to_relocate',
                'authorization_to_work'
            ]);
        });
    }
};
