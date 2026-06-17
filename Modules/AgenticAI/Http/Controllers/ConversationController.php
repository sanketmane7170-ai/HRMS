<?php

namespace Modules\AgenticAI\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\AgenticAI\Entities\Conversation;
use Modules\AgenticAI\Entities\Message;
use Modules\AgenticAI\Entities\AuditLog;
// use Modules\AgenticAI\Services\GeminiAgent;
use Modules\AgenticAI\Services\UnifiedAIAgent;
use Modules\AgenticAI\Services\ToolRegistryService;
use Carbon\Carbon;

class ConversationController extends Controller
{
    /**
     * List user's conversations grouped by time
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        $isArchived = $request->boolean('archived');
        
        $conversations = Conversation::forUser($user->id)
            ->where('is_archived', $isArchived)
            ->recent()
            ->with(['messages' => function($query) {
                $query->latest()->limit(1);
            }])
            ->get();

        // Group conversations by time
        $groups = [];
        
        $tempGroups = [
            'today' => ['title' => 'Today', 'items' => []],
            'yesterday' => ['title' => 'Yesterday', 'items' => []],
            'previous_7_days' => ['title' => 'Previous 7 Days', 'items' => []],
        ];

        $now = Carbon::now();
        $today = $now->copy()->startOfDay();
        $yesterday = $now->copy()->subDay()->startOfDay();
        $sevenDaysAgo = $now->copy()->subDays(7)->startOfDay();

        $olderGroups = [];

        foreach ($conversations as $conversation) {
            $updatedAt = Carbon::parse($conversation->updated_at);
            
            $item = [
                'id' => $conversation->id,
                'title' => $conversation->title,
                'updated_at' => $conversation->updated_at->toIso8601String(),
                'message_count' => $conversation->message_count,
                'is_archived' => $conversation->is_archived,
                'last_message' => $conversation->last_message ? [
                    'sender' => $conversation->last_message->sender,
                    'content' => mb_substr($conversation->last_message->content, 0, 100),
                ] : null
            ];

            if ($updatedAt->gte($today)) {
                $tempGroups['today']['items'][] = $item;
            } elseif ($updatedAt->gte($yesterday)) {
                $tempGroups['yesterday']['items'][] = $item;
            } elseif ($updatedAt->gte($sevenDaysAgo)) {
                $tempGroups['previous_7_days']['items'][] = $item;
            } else {
                $dateTitle = $updatedAt->format('M d, Y');
                if (!isset($olderGroups[$dateTitle])) {
                    $olderGroups[$dateTitle] = [
                        'title' => $dateTitle,
                        'items' => []
                    ];
                }
                $olderGroups[$dateTitle]['items'][] = $item;
            }
        }

        // Combine groups in order
        foreach (['today', 'yesterday', 'previous_7_days'] as $key) {
            if (!empty($tempGroups[$key]['items'])) {
                $groups[] = $tempGroups[$key];
            }
        }
        
        // Add older groups
        foreach ($olderGroups as $group) {
            $groups[] = $group;
        }

        return response()->json([
            'status' => 'success',
            'groups' => $groups,
            'total' => $conversations->count()
        ]);
    }

    /**
     * Create new conversation
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'initial_message' => 'required|string'
        ]);

        $user = auth()->user();

        //Sanket v2.0 - store() now only creates the conversation and saves the user message
        //AI response is fetched via streaming endpoint (/stream) which the frontend calls immediately after
        //This eliminates the synchronous blocking AI call that caused 10-15s initial load
        DB::beginTransaction();
        try {
            $conversation = Conversation::create([
                'user_id' => $user->id,
                'title'   => $this->generateTitle($request->initial_message),
            ]);

            $message = $conversation->messages()->create([
                'sender'  => Message::SENDER_USER,
                'content' => $request->initial_message,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'conversation' => [
                    'id'         => $conversation->id,
                    'title'      => $conversation->title,
                    'created_at' => $conversation->created_at->toIso8601String(),
                ],
                'message' => [
                    'id'         => $message->id,
                    'sender'     => $message->sender,
                    'content'    => $message->content,
                    'created_at' => $message->created_at->toIso8601String(),
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to create conversation', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create conversation',
                'debug'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get conversation details
     */
    public function show(int $id): JsonResponse
    {
        $conversation = Conversation::forUser(auth()->id())
            ->with('messages')
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'conversation' => [
                'id' => $conversation->id,
                'title' => $conversation->title,
                'is_archived' => $conversation->is_archived,
                'created_at' => $conversation->created_at->toIso8601String(),
                'updated_at' => $conversation->updated_at->toIso8601String()
            ],
            'messages' => $conversation->messages->map(function($message) {
                return [
                    'id' => $message->id,
                    'sender' => $message->sender,
                    'content' => $message->content,
                    'metadata' => $message->metadata,
                    'created_at' => $message->created_at->toIso8601String()
                ];
            })
        ]);
    }

    /**
     * Update conversation (title, archive status)
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'is_archived' => 'sometimes|boolean'
        ]);

        $conversation = Conversation::forUser(auth()->id())->findOrFail($id);
        $conversation->update($request->only(['title', 'is_archived']));

        return response()->json([
            'status' => 'success',
            'conversation' => [
                'id' => $conversation->id,
                'title' => $conversation->title,
                'is_archived' => $conversation->is_archived
            ]
        ]);
    }

    /**
     * Delete conversation
     */
    public function destroy(int $id): JsonResponse
    {
        $conversation = Conversation::forUser(auth()->id())->findOrFail($id);
        $conversation->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Conversation deleted successfully'
        ]);
    }

    /**
     * Send message in conversation
     */
    public function sendMessage(Request $request, int $id): JsonResponse
    {
        $request->validate([
            //Sanket v2.0 - cap message at 8000 chars: prevents context-window abuse and runaway API costs
            'content' => 'required|string|max:8000',
            'attachment_url' => 'nullable|url'
        ]);

        $conversation = Conversation::forUser(auth()->id())->findOrFail($id);
        $user = auth()->user();

        //Sanket v2.0 - save user message BEFORE the AI call, outside any transaction
        //Holding a DB transaction open during a 30-120s AI call exhausts the MySQL connection pool under load
        try {
            $messageData = [
                'sender'  => Message::SENDER_USER,
                'content' => $request->content,
            ];
            if ($request->attachment_url) {
                $messageData['metadata'] = ['attachment_url' => $request->attachment_url];
            }
            $userMessage = $conversation->messages()->create($messageData);

            //Sanket v2.0 - update title immediately after user message is saved (no AI call needed)
            if ($conversation->title === 'New Conversation' || empty($conversation->title)) {
                $conversation->update(['title' => $this->generateTitle($request->content)]);
            }
        } catch (\Exception $e) {
            \Log::error('sendMessage: failed to save user message', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => 'Failed to save message.'], 500);
        }

        //Sanket v2.0 - AI call runs outside any DB transaction to avoid connection pool exhaustion
        try {
            $agent    = new UnifiedAIAgent();
            $agent->setConversationId($conversation->id);
            $registry = new ToolRegistryService();

            //Sanket v2.0 - use only the current user message for intent routing
            $tools = $registry->getToolsForIntent($request->content ?? '');
            foreach ($tools as $tool) {
                $agent->registerTool($tool);
            }

            // Load conversation history (last 10 messages for context)
            $history = $conversation->messages()
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->reverse()
                ->values()
                ->map(function ($msg) {
                    return [
                        'role'     => $msg->sender === Message::SENDER_USER ? 'user' : 'assistant',
                        'content'  => $msg->content,
                        'metadata' => $msg->metadata,
                    ];
                })->toArray();

            $aiResponse = $agent->chat($history);
        } catch (\Exception $e) {
            //Sanket v2.0 - return 429 with friendly message for rate limit errors
            if (str_contains($e->getMessage(), 'Rate limit') || str_contains($e->getMessage(), 'rate_limit')) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'AI service is busy. Please try again in a few seconds.',
                    'retry'   => true,
                ], 429);
            }
            \Log::error('sendMessage: AI call failed', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

        //Sanket v2.0 - persist AI response in a short, isolated transaction
        try {
            $assistantMessage = $conversation->messages()->create([
                'sender'   => Message::SENDER_ASSISTANT,
                'content'  => $aiResponse,
                'metadata' => ['tools_available' => count($tools ?? [])],
            ]);
            $conversation->touch();

            if (config('agenticai.audit_enabled', false)) {
                AuditLog::logAction(
                    $conversation->id,
                    $user->id,
                    AuditLog::ACTION_MESSAGE_SENT,
                    ['user_message_id' => $userMessage->id, 'assistant_message_id' => $assistantMessage->id]
                );
            }
        } catch (\Exception $e) {
            \Log::error('sendMessage: failed to save AI response', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => 'AI responded but saving failed.'], 500);
        }

        return response()->json([
            'status'   => 'success',
            'messages' => [
                [
                    'id'         => $userMessage->id,
                    'sender'     => $userMessage->sender,
                    'content'    => $userMessage->content,
                    'created_at' => $userMessage->created_at->toIso8601String(),
                ],
                [
                    'id'         => $assistantMessage->id,
                    'sender'     => $assistantMessage->sender,
                    'content'    => $assistantMessage->content,
                    'metadata'   => $assistantMessage->metadata,
                    'created_at' => $assistantMessage->created_at->toIso8601String(),
                ],
            ],
        ]);
    }

    /**
     * Get messages for a conversation
     */
    public function getMessages(int $id, Request $request): JsonResponse
    {
        $conversation = Conversation::forUser(auth()->id())->findOrFail($id);
        
        //Sanket v2.0 - cap per_page to prevent DoS via huge pagination requests
        $perPage = min((int) $request->input('per_page', 50), 100);
        $messages = $conversation->messages()
            ->orderBy('created_at', 'asc')
            ->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'messages' => $messages->map(function($message) {
                return [
                    'id' => $message->id,
                    'sender' => $message->sender,
                    'content' => $message->content,
                    'metadata' => $message->metadata,
                    'created_at' => $message->created_at->toIso8601String()
                ];
            }),
            'pagination' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total()
            ]
        ]);
    }

    //Sanket v2.0 - generate conversation title from message text with no API call
    //Replaces $agent->summarize() which made a full OpenAI chat completion just for a 5-word title
    private function generateTitle(string $message): string
    {
        $clean = trim(strip_tags($message));
        // Remove markdown formatting artifacts
        $clean = preg_replace('/[#*_`>]/', '', $clean);
        $clean = preg_replace('/\s+/', ' ', $clean);
        $words = array_filter(explode(' ', $clean));
        $title = implode(' ', array_slice(array_values($words), 0, 6));
        return mb_strlen($title) > 0 ? $title : 'New Chat';
    }

    //Sanket v2.0 - streaming message endpoint: sends SSE tokens as they arrive from OpenAI
    //Frontend uses fetch() + ReadableStream so the user sees the response token-by-token
    public function streamMessage(Request $request, int $id): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        //Sanket v2.0 - cap message at 8000 chars: prevents context-window abuse and runaway OpenAI costs
        $request->validate(['content' => 'required|string|max:8000']);

        $conversation = Conversation::forUser(auth()->id())->findOrFail($id);
        $user         = auth()->user();
        $content      = $request->content;

        // Save user message immediately (before streaming starts)
        $userMessage = $conversation->messages()->create([
            'sender'  => Message::SENDER_USER,
            'content' => $content,
        ]);

        // Update title if still default
        if ($conversation->title === 'New Conversation' || empty($conversation->title)) {
            $conversation->update(['title' => $this->generateTitle($content)]);
        }

        // Build history
        $history = $conversation->messages()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->reverse()
            ->values()
            ->map(fn($msg) => [
                'role'    => $msg->sender === Message::SENDER_USER ? 'user' : 'assistant',
                'content' => $msg->content,
            ])->toArray();

        // Resolve tools
        $agent    = new UnifiedAIAgent();
        $agent->setConversationId($conversation->id);
        $registry = new ToolRegistryService();
        $tools    = $registry->getToolsForIntent($content);
        foreach ($tools as $tool) {
            $agent->registerTool($tool);
        }

        return response()->stream(function () use ($agent, $history, $conversation, $userMessage) {
            //Sanket v2.0 - disable PHP max_execution_time for streaming; OpenAI + tool calls can take >30s
            set_time_limit(0);

            // Disable output buffering so tokens reach the browser immediately
            if (ob_get_level()) {
                ob_end_clean();
            }

            $fullResponse = '';

            try {
                $agent->streamChat($history, function (string $token) use (&$fullResponse) {
                    $fullResponse .= $token;
                    //Sanket v2.0 - SSE format: each token emitted as data: {json}\n\n
                    echo 'data: ' . json_encode(['token' => $token]) . "\n\n";
                    flush();
                });
            } catch (\Exception $e) {
                Log::error('streamMessage: streaming failed', ['error' => $e->getMessage()]);
                echo 'data: ' . json_encode(['error' => 'AI service error. Please try again.']) . "\n\n";
                flush();
                return;
            }

            // Save the full assembled response to DB
            $assistantMessage = $conversation->messages()->create([
                'sender'  => Message::SENDER_ASSISTANT,
                'content' => $fullResponse ?: 'I was unable to generate a response.',
            ]);

            $conversation->touch();

            //Sanket v2.0 - send done event with message ID so frontend can attach copy button etc.
            echo 'data: ' . json_encode([
                'done'       => true,
                'message_id' => $assistantMessage->id,
            ]) . "\n\n";
            flush();
        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache, no-store',
            'X-Accel-Buffering' => 'no',   // Disable nginx buffering
            'Connection'        => 'keep-alive',
        ]);
    }

    //Sanket v2.0 - serves AI-generated files (excel/pdf/csv/docx) with download headers; files live in public/generated-docs/
    public function downloadFile(Request $request, string $filename): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        // Sanitize filename: only allow alphanumeric, dash, underscore, dot
        if (!preg_match('/^[\w\-]+\.(xlsx|pdf|csv|docx)$/i', $filename)) {
            abort(400, 'Invalid filename.');
        }

        $path = public_path('generated-docs/' . $filename);

        if (!file_exists($path)) {
            abort(404, 'File not found.');
        }

        $mimeTypes = [
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'pdf'  => 'application/pdf',
            'csv'  => 'text/csv',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];
        $ext  = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mime = $mimeTypes[$ext] ?? 'application/octet-stream';

        return response()->download($path, $filename, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
