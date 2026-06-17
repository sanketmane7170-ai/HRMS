<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepartmentAllowancesTable extends Migration
{
    public function up(): void
    {
        Schema::create('department_allowances', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('department_id');
            $table->string('allowance_name'); // e.g., HRA, Transport, etc.

            $table->enum('type', ['monthly', 'yearly', 'one_time'])->default('monthly'); // Frequency
            $table->enum('allowance_type', ['fixed', 'percentage'])->default('fixed');   // Calculation basis

            $table->decimal('amount', 10, 2)->default(0.00); // Value or percentage

            $table->timestamps();

            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_allowances');
    }
}
