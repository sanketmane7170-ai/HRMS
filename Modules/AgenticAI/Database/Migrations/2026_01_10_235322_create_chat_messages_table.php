<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Fix for migration order conflict: If tables already renamed, skip this.
        if (Schema::hasTable('ai_messages') || Schema::hasTable('ai_conversations')) {
            return;
        }

        if (!Schema::hasTable('a_i_chat_messages')) {
            Schema::create('a_i_chat_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('chat_session_id')->index();
                $table->string('role'); // user, assistant, system, tool
                $table->longText('content')->nullable();
                $table->json('metadata')->nullable(); // For tool calls, arguments, tool_call_id
                $table->timestamps();
    
                $table->foreign('chat_session_id')->references('id')->on('a_i_chat_sessions')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('a_i_chat_messages');
    }
};
