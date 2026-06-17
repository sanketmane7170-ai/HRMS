<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('ip_investment_declarations')) {
            Schema::create('ip_investment_declarations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('declaration_id');
                $table->foreign('declaration_id', 'ip_invest_decl_fk')->references('id')->on('ip_employee_tax_declarations')->onDelete('cascade');
                $table->string('section_code', 10); // 80C, 80CCD1B, 80D, 80E, 80G, 80TTA, 24B
                $table->decimal('declared_amount', 12, 2)->default(0);
                $table->string('proof_path')->nullable();
                $table->decimal('verified_amount', 12, 2)->nullable();
                $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('verified_at')->nullable();
                $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
                $table->timestamps();

                $table->unique(['declaration_id', 'section_code'], 'ip_invest_decl_section_unique');
            });
        }

        if (! Schema::hasTable('ip_hra_exemption_inputs')) {
            Schema::create('ip_hra_exemption_inputs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('declaration_id');
                $table->foreign('declaration_id', 'ip_hra_decl_fk')->references('id')->on('ip_employee_tax_declarations')->onDelete('cascade');
                $table->decimal('monthly_rent', 10, 2)->default(0);
                $table->boolean('is_metro')->default(false); // metro = 50% of basic exemption slab, non-metro = 40%
                $table->string('landlord_pan', 10)->nullable(); // mandatory if annual rent > 1,00,000
                $table->string('landlord_name')->nullable();
                $table->timestamps();

                $table->unique('declaration_id', 'ip_hra_decl_unique');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('ip_hra_exemption_inputs');
        Schema::dropIfExists('ip_investment_declarations');
    }
};
