<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ip_statutory_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type');       // e.g. PfSetting, IncomeTaxSlab
            $table->unsignedBigInteger('entity_id');
            $table->string('action');             // created / updated / deleted
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->foreign('changed_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ip_statutory_audit_logs');
    }
};
