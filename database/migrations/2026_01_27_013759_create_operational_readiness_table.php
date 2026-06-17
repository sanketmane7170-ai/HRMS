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
        Schema::create('operational_readiness', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('branch_notification_sent_at')->nullable();
            $table->boolean('it_login_created')->default(false);
            $table->boolean('email_created')->default(false);
            $table->string('uniform_status')->default('pending'); // pending, ordered, delivered
            $table->boolean('induction_completed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operational_readiness');
    }
};
