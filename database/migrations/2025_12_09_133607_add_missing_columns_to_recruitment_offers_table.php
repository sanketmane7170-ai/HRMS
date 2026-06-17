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
            $table->string('currency', 10)->default('USD')->after('salary');
            $table->string('payment_period', 20)->default('Year')->after('currency');
            $table->string('pay_frequency', 20)->default('Monthly')->after('payment_period');
            $table->text('benefits')->nullable()->after('pay_frequency');
            $table->date('start_date')->nullable()->after('joining_date');
            $table->text('notes')->nullable()->after('terms_conditions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recruitment_offers', function (Blueprint $table) {
            $table->dropColumn([
                'currency', 
                'payment_period', 
                'pay_frequency', 
                'benefits',
                'start_date',
                'notes',
                'created_by'
            ]);
        });
    }
};
