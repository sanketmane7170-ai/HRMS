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
        Schema::create('employee_taxes', function (Blueprint $table) {
            $table->id();
            $table->string('taxtype');
            $table->string('taxunit');
            $table->decimal('taxamount', 10, 2); // Assuming a decimal field for tax amount
       
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_taxes');
    }
};
