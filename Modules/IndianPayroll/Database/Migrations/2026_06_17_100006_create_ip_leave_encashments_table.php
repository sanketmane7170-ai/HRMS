<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ip_leave_encashments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->decimal('days', 8, 2);
            $table->decimal('per_day_rate', 10, 2);
            $table->decimal('amount', 12, 2);
            // Mid-service leave encashment is fully taxable for non-government
            // employees; kept as a column so policy can vary it per record.
            $table->decimal('taxable_amount', 12, 2)->default(0);
            $table->string('remarks')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'paid'])->default('pending');
            $table->foreignId('run_id')->nullable()->constrained('ip_payroll_runs')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'month', 'year', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ip_leave_encashments');
    }
};
