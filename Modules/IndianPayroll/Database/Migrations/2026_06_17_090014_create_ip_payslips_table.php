<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('ip_payslips')) {
            Schema::create('ip_payslips', function (Blueprint $table) {
                $table->id();
                $table->foreignId('run_id')->constrained('ip_payroll_runs')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

                $table->decimal('gross_earnings', 12, 2)->default(0);
                $table->decimal('total_statutory_deductions', 12, 2)->default(0);
                $table->decimal('total_other_deductions', 12, 2)->default(0);
                $table->decimal('total_employer_contributions', 12, 2)->default(0);
                $table->decimal('net_pay', 12, 2)->default(0);

                $table->integer('days_in_period')->default(0);
                $table->decimal('paid_days', 6, 2)->default(0); // present + paid leave + holiday
                $table->decimal('loss_of_pay_days', 6, 2)->default(0);

                $table->string('tax_regime', 3)->nullable(); // old/new, snapshot at time of computation
                $table->enum('status', ['draft', 'computed', 'approved', 'locked'])->default('draft');

                $table->timestamps();

                $table->unique(['run_id', 'user_id'], 'ip_payslips_run_user_unique');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('ip_payslips');
    }
};
