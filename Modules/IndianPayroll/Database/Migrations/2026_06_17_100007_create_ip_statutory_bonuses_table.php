<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ip_statutory_bonuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('financial_year', 9); // e.g. 2025-2026
            $table->decimal('monthly_wage', 12, 2);       // basic + DA used for eligibility
            $table->decimal('bonus_wage_base', 12, 2);    // capped wage the % is applied to
            $table->decimal('percentage', 5, 2);          // 8.33 .. 20
            $table->unsignedTinyInteger('months_eligible')->default(12);
            $table->decimal('bonus_amount', 12, 2);
            $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
            $table->timestamps();

            $table->unique(['user_id', 'financial_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ip_statutory_bonuses');
    }
};
