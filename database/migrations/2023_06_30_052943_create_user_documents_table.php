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
        Schema::create('user_documents', function (Blueprint $table) {
            $table->id();
            $table->string('original_name');
            $table->string('path');
            $table->string('serial_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->enum('type', ['passport', 'visa', 'eid_front', 'eid_back', 'insurance', 'education', 'license', 'other'])->default('other');
            $table->foreignId('user_id')->constrained()->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_documents');
    }
};
