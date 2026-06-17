<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ip_reimbursements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('reimbursement_type', [
                'travel', 'hotel', 'mobile', 'fuel', 'internet', 'medical', 'other',
            ])->default('other');
            $table->decimal('claim_amount', 12, 2);
            // Portion of the claim that is taxable in the employee's hands. 0 for a
            // fully tax-free reimbursement, == claim_amount for a fully taxable one.
            $table->decimal('taxable_amount', 12, 2)->default(0);
            $table->date('claim_date');
            $table->string('description')->nullable();
            $table->string('proof_path')->nullable();
            // pending -> approved -> paid (paid set when the linked run is approved).
            // rejected is terminal.
            $table->enum('status', ['pending', 'approved', 'rejected', 'paid'])->default('pending');
            // Set when the approved claim is picked up by a payroll run so it is
            // never paid twice; cleared if that run is deleted.
            $table->foreignId('run_id')->nullable()->constrained('ip_payroll_runs')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ip_reimbursements');
    }
};
