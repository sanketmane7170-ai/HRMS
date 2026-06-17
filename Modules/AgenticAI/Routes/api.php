<?php

use Modules\AgenticAI\Http\Controllers\ConversationController;

Route::prefix('ai')->middleware(['auth'])->group(function() {
    // Read operations (Standard API throttle)
    Route::middleware('throttle:api')->group(function() {
        Route::get('/conversations', [ConversationController::class, 'index']);
        Route::get('/conversations/{id}', [ConversationController::class, 'show']);
        Route::get('/conversations/{id}/messages', [ConversationController::class, 'getMessages']);
        
        // Lightweight write operations
        Route::patch('/conversations/{id}', [ConversationController::class, 'update']);
        Route::delete('/conversations/{id}', [ConversationController::class, 'destroy']);

        //Sanket v2.0 - download route: serves AI-generated files with proper Content-Disposition headers
        Route::get('/download/{filename}', [ConversationController::class, 'downloadFile']);
    });
    
    // AI Generation operations (Strict AI throttle)
    Route::middleware('throttle.ai')->group(function() {
        Route::post('/conversations', [ConversationController::class, 'store']);
        Route::post('/conversations/{id}/messages', [ConversationController::class, 'sendMessage']);
        //Sanket v2.0 - streaming endpoint: SSE token-by-token response, browser sees output in ~1s
        Route::post('/conversations/{id}/stream', [ConversationController::class, 'streamMessage']);
        Route::post('/upload', [\Modules\AgenticAI\Http\Controllers\AgenticAIController::class, 'uploadFile']);
        Route::match(['get', 'post'], '/tts', [\Modules\AgenticAI\Http\Controllers\ChatController::class, 'textToSpeech']);
    });
});
