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
        // Rename tables with safety checks
        if (Schema::hasTable('a_i_chat_sessions') && !Schema::hasTable('ai_conversations')) {
            Schema::rename('a_i_chat_sessions', 'ai_conversations');
        }
        
        if (Schema::hasTable('a_i_chat_messages') && !Schema::hasTable('ai_messages')) {
            Schema::rename('a_i_chat_messages', 'ai_messages');
        }

        // Add new columns to conversations
        Schema::table('ai_conversations', function (Blueprint $table) {
            if (!Schema::hasColumn('ai_conversations', 'is_archived')) {
                $table->boolean('is_archived')->default(false)->after('title');
            }
        });

        // Update messages table structure
        Schema::table('ai_messages', function (Blueprint $table) {
            // Rename chat_session_id to conversation_id
            if (Schema::hasColumn('ai_messages', 'chat_session_id')) {
                $table->renameColumn('chat_session_id', 'conversation_id');
            }
            
            // Change 'role' to 'sender'
            if (Schema::hasColumn('ai_messages', 'role')) {
                $table->renameColumn('role', 'sender');
            }
            
            // Ensure metadata column exists
            if (!Schema::hasColumn('ai_messages', 'metadata')) {
                $table->json('metadata')->nullable()->after('content');
            }
        });

        // Create audit log table (optional but recommended)
        Schema::create('ai_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('user_id');
            $table->string('action', 100)->comment('message_sent, tool_executed, approval_requested');
            $table->json('details')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('conversation_id');
            $table->index('user_id');
            $table->index('created_at');

            $table->foreign('conversation_id')
                ->references('id')
                ->on('ai_conversations')
                ->onDelete('cascade');
            
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop audit log
        Schema::dropIfExists('ai_audit_logs');

        // Revert messages table
        Schema::table('ai_messages', function (Blueprint $table) {
            $table->renameColumn('sender', 'role');
            $table->renameColumn('conversation_id', 'chat_session_id');
        });

        // Revert conversations table
        Schema::table('ai_conversations', function (Blueprint $table) {
            $table->dropColumn('is_archived');
        });

        // Rename tables back
        Schema::rename('ai_messages', 'a_i_chat_messages');
        Schema::rename('ai_conversations', 'a_i_chat_sessions');
    }
};
