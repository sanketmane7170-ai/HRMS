<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('ip_lwf_rules')) {
            Schema::create('ip_lwf_rules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('state_id')->constrained('ip_states')->onDelete('cascade');
                $table->enum('frequency', ['monthly', 'half_yearly', 'annual'])->default('half_yearly');
                $table->decimal('employee_contribution', 10, 2);
                $table->decimal('employer_contribution', 10, 2);
                $table->decimal('wage_ceiling', 12, 2)->nullable(); // some states exempt employees above a wage ceiling
                $table->date('effective_from');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['state_id', 'is_active', 'effective_from'], 'ip_lwf_rules_lookup_idx');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('ip_lwf_rules');
    }
};
