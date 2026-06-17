<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ai_tool_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('conversation_id')->nullable();
            $table->string('tool_name', 100);
            $table->json('payload')->nullable();
            $table->json('response')->nullable();
            $table->string('status', 20)->default('success');
            $table->integer('duration_ms')->default(0);
            $table->timestamp('created_at')->nullable();

            $table->index('user_id');
            $table->index('conversation_id');
            $table->index('tool_name');
            $table->index('status');
            $table->index('created_at');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            
            $table->foreign('conversation_id')
                ->references('id')
                ->on('ai_conversations')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_tool_logs');
    }
};
