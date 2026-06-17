<?php

namespace Modules\AgenticAI\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\AgenticAI\Services\UnifiedAIAgent;
use Modules\AgenticAI\Traits\ResolvesSSLCertificate;
use Modules\AgenticAI\Tools\GetSystemTimeTool;

class ChatController extends Controller
{
    use ResolvesSSLCertificate;
    public function chat(Request $request)
    {
        // Increase execution time to handle AI + Database operations
        set_time_limit(120);

        $request->validate([
            'message' => 'required|string',
            'session_id' => 'nullable|exists:a_i_chat_sessions,id'
        ]);

        $message = $request->input('message');
        $sessionId = $request->input('session_id');
        $user = auth()->user();

        // 1. Get or Create Session
        if (!$sessionId) {
            $session = \Modules\AgenticAI\Entities\ChatSession::create([
                'user_id' => $user->id,
                'title' => substr($message, 0, 50) . '...' // Simple auto-title
            ]);
            $sessionId = $session->id;
        } else {
            $session = \Modules\AgenticAI\Entities\ChatSession::find($sessionId);
            // Security Check
            if ($session->user_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }

        // 2. Save User Message
        $session->messages()->create([
            'role' => 'user',
            'content' => $message
        ]);
        
        // Invalidate Cache after new message
        \Illuminate\Support\Facades\Cache::forget("ai_conv_history_{$sessionId}");

        // 3. Load History with Caching
        $cacheKey = "ai_conv_history_{$sessionId}";
        $history = \Illuminate\Support\Facades\Cache::remember($cacheKey, 600, function() use ($session) {
            return $session->messages()
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($msg) {
                    return [
                        'role' => $msg->role,
                        'content' => $msg->content,
                    ];
                })->toArray();
        });
        
        // Limit context size for API (last 10 messages)
        $contextHistory = array_slice($history, -10);

        // --- HINT INJECTION FOR APPROVALS ---
        //Sanket v2.0 - only inject approval hint if the PREVIOUS message from AI was actually asking for confirmation
        if (preg_match('/^(yes|approve|confirm|proceed|ok|continue|yep|sure)$/i', trim($message))) {
            // Check that the previous AI message actually asked for confirmation
            $previousMessages = array_filter($contextHistory, fn($m) => $m['role'] === 'assistant');
            $lastAIMessage = end($previousMessages);
            if ($lastAIMessage && preg_match('/confirm|approve|proceed|do you want|shall i|would you like/i', $lastAIMessage['content'] ?? '')) {
                $contextHistory[] = [
                    'role' => 'system',
                    'content' => "The user has confirmed the previous request. Please execute the pending action now."
                ];
            }
        }

        // 4. Run Agent with Automatic Fallback (OpenAI → Gemini)
        $agent = new UnifiedAIAgent();
        $agent->setConversationId($sessionId);
        $registry = new \Modules\AgenticAI\Services\ToolRegistryService();
        
        // Use a broader intent context (Current message + Last 2 messages)
        $intentContext = $message;
        $recentHistory = array_slice($history, -10);
        foreach ($recentHistory as $msg) {
            $intentContext .= " " . $msg['content'];
        }
        
        $tools = $registry->getToolsForIntent($intentContext);
        
        foreach ($tools as $tool) {
            $agent->registerTool($tool);
        }

        
        $isVoice = $request->input('is_voice', false);
        $voiceLang = $request->input('voice_language', 'en-US');

        try {
            // Pass options array with voice context
            $responseContent = $agent->chat($contextHistory, [
                'is_voice' => $isVoice,
                'user_language' => $voiceLang
            ]); 

            // 5. Save Assistant Response
            $session->messages()->create([
                'role' => 'assistant',
                'content' => $responseContent
            ]);

            // Invalidate Cache after new assistant message
            \Illuminate\Support\Facades\Cache::forget("ai_conv_history_{$sessionId}");

            return response()->json([
                'status' => 'success',
                'reply' => $responseContent,
                'session_id' => $session->id,
                'title' => $session->title
            ]);

        } catch (\Exception $e) {
            //Sanket v2.0 - don't leak internal error details to client
            \Log::error('AI Chat Error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }

    public function getHistory()
    {
        $sessions = \Modules\AgenticAI\Entities\ChatSession::where('user_id', auth()->id())
            ->orderBy('updated_at', 'desc')
            ->take(20)
            ->get(['id', 'title', 'created_at']);

        return response()->json([
            'status' => 'success',
            'data' => $sessions
        ]);
    }

    public function getSessionMessages($id)
    {
        $session = \Modules\AgenticAI\Entities\ChatSession::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return response()->json([
            'status' => 'success',
            'data' => $session->messages()->orderBy('created_at', 'asc')->get()
        ]);
    }

    /**
     * Generate speech from text using OpenAI.
     */
    public function textToSpeech(Request $request)
    {
        $request->validate([
            'text' => 'required|string|max:4096',
            'voice' => 'nullable|string|in:alloy,echo,fable,onyx,nova,shimmer'
        ]);

        $text = $request->input('text');
        $voice = $request->input('voice', 'nova'); // 'nova' is a good helper voice

        //Sanket v2.0 - use config() instead of env() for TTS API key
        $apiKey = config('services.openai.api_key');

        try {
            //Sanket v2.0 - set CA cert path for SSL verification on Docker/Windows
            $guzzleOptions = $this->getSSLOptions();

            $client = new \GuzzleHttp\Client($guzzleOptions);
            $response = $client->post('https://api.openai.com/v1/audio/speech', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'tts-1-hd', // Upgrade to HD for better quality
                    'input' => $text,
                    'voice' => $voice,
                ],
                // Stream the response
                'stream' => true,
            ]);

            return response()->stream(function() use ($response) {
                $stream = $response->getBody();
                while (!$stream->eof()) {
                    echo $stream->read(1024);
                }
            }, 200, [
                'Content-Type' => 'audio/mpeg',
                'Cache-Control' => 'no-cache',
                'X-Accel-Buffering' => 'no', // Disable Nginx buffering
            ]);

        } catch (\Exception $e) {
            //Sanket v2.0 - don't leak internal error messages to the client
            \Log::error('OpenAI TTS Error details: ' . $e->getMessage());
            if ($e instanceof \GuzzleHttp\Exception\ClientException) {
                \Log::error('Response: ' . $e->getResponse()->getBody()->getContents());
            }
            return response()->json(['error' => 'TTS generation failed. Please try again later.'], 500);
        }
    }
}
