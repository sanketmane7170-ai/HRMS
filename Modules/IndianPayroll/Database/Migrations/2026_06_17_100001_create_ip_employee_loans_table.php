<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ip_employee_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('loan_type', ['salary_advance', 'personal_loan', 'emergency_loan'])->default('personal_loan');
            $table->decimal('principal_amount', 12, 2);
            $table->decimal('emi_amount', 12, 2);
            // Month the first EMI should be recovered from.
            $table->unsignedTinyInteger('start_month'); // 1-12
            $table->unsignedSmallInteger('start_year');
            $table->date('disbursed_on')->nullable();
            // active = recoveries ongoing, closed = fully recovered, cancelled = written off / stopped.
            $table->enum('status', ['active', 'closed', 'cancelled'])->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        // Per-run recovery ledger — the single source of truth for how much has
        // been recovered. Keeping it as its own table (rather than a counter on
        // the loan) makes recompute idempotent: a run's recovery is replaced via
        // updateOrCreate on (loan_id, run_id), never double-counted.
        Schema::create('ip_loan_recoveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('ip_employee_loans')->onDelete('cascade');
            $table->foreignId('run_id')->constrained('ip_payroll_runs')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->timestamps();

            $table->unique(['loan_id', 'run_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ip_loan_recoveries');
        Schema::dropIfExists('ip_employee_loans');
    }
};
