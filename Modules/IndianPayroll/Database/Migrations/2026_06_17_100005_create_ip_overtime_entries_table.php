<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ip_overtime_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            // The payroll month/year this overtime belongs to — compute pays it in
            // the matching run.
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->enum('entry_type', ['overtime', 'comp_off'])->default('overtime');
            $table->decimal('hours', 8, 2)->default(0);       // overtime: hours worked; comp_off: days paid
            $table->decimal('rate_per_unit', 10, 2)->default(0); // per hour (overtime) / per day (comp_off)
            $table->decimal('amount', 12, 2);                  // hours * rate, or a flat amount
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
        Schema::dropIfExists('ip_overtime_entries');
    }
};
