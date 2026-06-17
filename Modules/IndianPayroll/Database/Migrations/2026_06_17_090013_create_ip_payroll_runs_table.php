<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('ip_payroll_runs')) {
            Schema::create('ip_payroll_runs', function (Blueprint $table) {
                $table->id();
                $table->unsignedTinyInteger('month'); // 1-12
                $table->unsignedSmallInteger('year');
                $table->date('period_start');
                $table->date('period_end');
                $table->enum('status', ['draft', 'computed', 'approved', 'locked'])->default('draft');
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('approved_at')->nullable();
                $table->foreignId('locked_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('locked_at')->nullable();
                $table->timestamps();

                $table->unique(['month', 'year'], 'ip_payroll_runs_month_year_unique');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('ip_payroll_runs');
    }
};
