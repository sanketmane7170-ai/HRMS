<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('ip_pt_slabs')) {
            Schema::create('ip_pt_slabs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('state_id')->constrained('ip_states')->onDelete('cascade');
                $table->enum('gender', ['all', 'male', 'female'])->default('all'); // some states (e.g. Karnataka historically) vary by gender; most use 'all'
                $table->decimal('salary_from', 12, 2);
                $table->decimal('salary_to', 12, 2)->nullable(); // null = no upper bound
                $table->decimal('monthly_tax', 10, 2);
                $table->enum('frequency', ['monthly', 'annual'])->default('monthly'); // Maharashtra charges an extra one-time annual slab in addition to monthly
                $table->date('effective_from');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['state_id', 'is_active', 'effective_from'], 'ip_pt_slabs_lookup_idx');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('ip_pt_slabs');
    }
};
