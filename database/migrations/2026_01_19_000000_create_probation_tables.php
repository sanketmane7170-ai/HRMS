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
        if (!Schema::hasTable('probation_letters')) {
            Schema::create('probation_letters', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->longText('content')->nullable();
                $table->string('file_path')->nullable();
                $table->string('pdf_path')->nullable();
                $table->string('status')->default('pending');
                $table->timestamp('email_sent_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('probation_letter_templates')) {
            Schema::create('probation_letter_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->longText('content');
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('probation_letter_templates');
        Schema::dropIfExists('probation_letters');
    }
};
