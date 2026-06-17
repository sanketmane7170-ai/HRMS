<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('ip_income_tax_slabs')) {
            Schema::create('ip_income_tax_slabs', function (Blueprint $table) {
                $table->id();
                $table->string('financial_year', 9); // e.g. 2025-2026
                $table->enum('regime', ['old', 'new']);
                $table->decimal('slab_from', 14, 2);
                $table->decimal('slab_to', 14, 2)->nullable(); // null = no upper bound
                $table->decimal('rate', 5, 2); // percentage
                $table->timestamps();

                $table->index(['financial_year', 'regime'], 'ip_tax_slabs_lookup_idx');
            });
        }

        if (! Schema::hasTable('ip_income_tax_surcharge_slabs')) {
            Schema::create('ip_income_tax_surcharge_slabs', function (Blueprint $table) {
                $table->id();
                $table->string('financial_year', 9);
                $table->enum('regime', ['old', 'new']);
                $table->decimal('income_from', 14, 2);
                $table->decimal('income_to', 14, 2)->nullable();
                $table->decimal('surcharge_rate', 5, 2); // percentage of base tax
                $table->timestamps();

                $table->index(['financial_year', 'regime'], 'ip_surcharge_slabs_lookup_idx');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('ip_income_tax_surcharge_slabs');
        Schema::dropIfExists('ip_income_tax_slabs');
    }
};
