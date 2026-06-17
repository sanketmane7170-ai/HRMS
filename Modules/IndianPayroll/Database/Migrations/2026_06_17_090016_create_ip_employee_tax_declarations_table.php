<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('ip_employee_tax_declarations')) {
            Schema::create('ip_employee_tax_declarations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('financial_year', 9);
                $table->enum('regime_choice', ['old', 'new'])->default('new');
                $table->decimal('income_from_previous_employer', 14, 2)->default(0);
                $table->decimal('tds_deducted_by_previous_employer', 14, 2)->default(0);
                $table->timestamp('regime_locked_at')->nullable(); // null = still editable; locked after the configured cutoff
                $table->timestamps();

                $table->unique(['user_id', 'financial_year'], 'ip_tax_decl_user_fy_unique');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('ip_employee_tax_declarations');
    }
};
