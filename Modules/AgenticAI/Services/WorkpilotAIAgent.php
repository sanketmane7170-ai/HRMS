<?php

namespace Modules\AgenticAI\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\AgenticAI\Interfaces\ToolInterface;
use Modules\AgenticAI\Traits\ResolvesSSLCertificate;

/**
 * WorkpilotAIAgent - Direct HTTP implementation for Flowversal (Workpilot AI)
 * 
 * Author: Sanket
 */
class WorkpilotAIAgent
{
    use ResolvesSSLCertificate;
    protected ?string $baseUrl = '';
    protected ?string $apiKey = '';
    protected ?string $model = '';
    protected array $tools = [];
    protected array $systemInstructions = [];
    protected ?int $conversationId = null;

    public function __construct()
    {
        // Author: Sanket - Configuration for Flowversal server
        $this->baseUrl = config('services.flowversal.base_url') ?? '';
        $this->apiKey = config('services.flowversal.api_key') ?? '';
        $this->model = config('services.flowversal.model', 'qwen2.5:14b') ?? 'qwen2.5:14b';
        
        // Get authenticated user info
        $user = auth()->user();
        $now = now();
        $userContext = $user ? "You are assisting {$user->name} (Employee ID: {$user->id}, Email: {$user->email})." : "You are assisting a user.";
        $timeContext = "Current Server Time: " . $now->format('l, F j, Y, g:i A') . " (Timezone: " . config('app.timezone') . ")";
        
        //Sanket v2.0 - system prompt with ReAct reasoning, schema awareness, and general knowledge fallback
        $this->systemInstructions = [
            "You are Workpilot AI, an intelligent agentic HR assistant with full system access.",
            "YOUR PRIMARY CAPABILITIES are HR tools listed in the 'DYNAMIC CAPABILITIES' section below.",
            "For HR-related actions: ALWAYS prefer using the available tools.",
            "For general questions (greetings, world knowledge, math, etc.): Answer naturally and helpfully using your knowledge.",
            $userContext,
            $timeContext,
            "LANGUAGE: Always respond in ENGLISH.",
            
            "=== REASONING PATTERN (ReAct) ===",
            "For complex questions: THOUGHT -> ACTION (tool call) -> OBSERVATION (analyze result) -> ANSWER.",
            "If a tool or query fails, SELF-CORRECT: read the error, fix the approach, retry.",
            
            "=== CORE AGENTIC RULES (STRICT) ===",
            "1. NO FAKING: DO NOT say 'I have started' or 'Initiating'. You MUST call the tool.",
            "2. ACTION-ONLY: When calling a tool, your entire response should strictly be the tool call. No conversation before.",
            "3. TOOL HONESTY: If a required tool is not listed, use explore_database or general knowledge.",
            "4. NO LOOPING: If you see '[DONE]' in history, move on to the next step.",
            "5. KNOWLEDGE FALLBACK: If policy/knowledge search returns NO results, answer from general knowledge. State it's general knowledge, not company policy.",
            "6. DATABASE EXPLORER: You have explore_database with full schema in your context. Write SELECT queries directly — you already know all tables and columns.",
            
            "=== TOOL CALL FORMAT (MANDATORY) ===",
            "You MUST use this exact format to trigger actions:",
            "[TOOL_CALL: tool_name|{\"arg\": \"value\"}]",
            
            "=== YOUR CAPABILITIES (Current Session) ===",
            "%DYNAMIC_CAPABILITIES%",
            
            "%SCHEMA_CONTEXT%",
            
            "=== TONE ===",
            "Efficient Agent. Actions first.",
        ];
    }

    /**
     * Build dynamic capabilities from registered tools
     * Author: Sanket
     */
    protected function buildCapabilitiesList(): string
    {
        if (empty($this->tools)) {
            return "No specialized tools registered for this intent. Use general knowledge or ask for clarification.";
        }

        $list = "";
        foreach ($this->tools as $tool) {
            $list .= "- `{$tool->name()}`: {$tool->description()}\n";
        }
        return $list;
    }

    public function registerTool(ToolInterface $tool): self
    {
        $this->tools[$tool->name()] = $tool;
        return $this;
    }

    public function setConversationId(int $id): self
    {
        $this->conversationId = $id;
        return $this;
    }

    /**
     * Main chat method with tool calling support
     * Author: Sanket
     */
    public function chat(array $messages, array $options = [])
    {
        try {
            return $this->performChat($messages, $options);
        } catch (\Exception $e) {
            Log::error('Workpilot AI (Flowversal) API failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e; // Rethrow for UnifiedAIAgent fallback logic
        }
    }

    protected function performChat(array $messages, array $options = [])
    {
        // Author: Sanket - Increase execution time for potentially slow AI/Tool loops
        set_time_limit(300);
        
        // 1. Fetch RAG Context - Author: Sanket
        $ragContext = "";
        try {
            $lastMessage = end($messages);
            $userQuestion = is_array($lastMessage['content']) 
                ? $lastMessage['content'][0]['text'] ?? '' 
                : $lastMessage['content'];

            //Sanket v2.0 - use config() instead of env() so it works with cached config
            $ragEnabled = config('services.ai.rag_enabled', true);
            $cleanQuestion = strtolower(trim($userQuestion));
            $greetings = ['hi', 'hello', 'hey', 'hello ai', 'good morning', 'good afternoon', 'good evening', 'who are you', 'what are you'];
            
            if (!$ragEnabled) {
                Log::info("RAG Context disabled via config");
            } elseif (strlen($cleanQuestion) < 10 || in_array($cleanQuestion, $greetings)) {
                Log::info("Skipping RAG Context for short/greeting message", ['question' => $cleanQuestion]);
            } else {
                Log::info("Fetching RAG Context for Workpilot AI", ['question' => substr($userQuestion, 0, 50)]);
                
                $ragUrl = config('services.rag.url', 'http://46.225.69.121:8001');
                $ragResponse = Http::timeout(5)->post("{$ragUrl}/query", [ //Sanket v2.0 - increased timeout from 1s to 5s, 1s was causing RAG to always fail
                    'question' => $userQuestion,
                    'top_k' => 3,
                ]);

                if ($ragResponse->successful()) {
                    $ragData = $ragResponse->json();
                    if (!empty($ragData['sources'])) {
                        $ragContext = "=== COMPANY KNOWLEDGE BASE CONTEXT ===\n";
                        foreach ($ragData['sources'] as $source) {
                            $ragContext .= "- " . ($source['text'] ?? '') . "\n";
                        }
                        $ragContext .= "======================================\n";
                        Log::info("RAG Context Injected into Workpilot AI", ['sources' => count($ragData['sources'])]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning("RAG context injection failed for Workpilot AI: " . $e->getMessage());
        }

        // 2. Prepare formatted messages
        $capabilities = $this->buildCapabilitiesList();
        
        //Sanket v2.0 - inject full database schema so AI can write queries in 1 shot
        $schemaContext = '';
        try {
            $schemaProvider = new SchemaContextProvider();
            $schemaContext = $schemaProvider->getSchemaContext() . "\n" . $schemaProvider->getRelationshipMap();
        } catch (\Exception $e) {
            Log::warning('WorkpilotAIAgent: Schema context injection failed', ['error' => $e->getMessage()]);
        }
        
        $finalInstructions = array_map(function($line) use ($capabilities, $schemaContext) {
            $line = str_replace('%DYNAMIC_CAPABILITIES%', $capabilities, $line);
            $line = str_replace('%SCHEMA_CONTEXT%', $schemaContext, $line);
            return $line;
        }, $this->systemInstructions);

        $formattedMessages = [
            ['role' => 'system', 'content' => implode("\n", $finalInstructions)]
        ];
        
        if ($ragContext) {
            $formattedMessages[] = ['role' => 'system', 'content' => $ragContext];
        }

        foreach ($messages as $msg) {
            // Author: Sanket - Neutralize tool markers and historical system results so AI doesn't re-execute or obsess
            $content = $msg['content'];
            if ($msg['role'] === 'assistant') {
                $content = preg_replace('/\[TOOL_CALL:.*?\]/s', '[DONE]', $content);
            }
            if ($msg['role'] === 'system' && str_contains($content, 'TOOL RESULT')) {
                 $content = "[Historical Data: " . preg_replace('/\{.*?\}/s', '{...}', $content) . "]";
            }
            
            $formattedMessages[] = [
                'role' => $msg['role'],
                'content' => $content
            ];
        }

        // Prepare tools in OpenAI format
        $toolDefinitions = [];
        foreach ($this->tools as $tool) {
            $toolDefinitions[] = [
                'type' => 'function',
                'function' => [
                    'name' => $tool->name(),
                    'description' => $tool->description(),
                    'parameters' => $tool->schema(),
                ]
            ];
        }

        $iterations = 0;
        $maxIterations = 10;
        $executedTools = []; // Author: Sanket - Track executed tools to prevent loops

        while ($iterations < $maxIterations) {
            $iterations++;
            Log::info("Workpilot AI Thinking (Iteration $iterations)...", ['message_count' => count($formattedMessages)]);

            // Build HTTP client
            //Sanket v2.0 - add SSL cert resolution for Docker/Windows
            $httpClient = Http::timeout(180)->connectTimeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json'
                ]);
            $sslOptions = $this->getSSLOptions();
            if (!empty($sslOptions)) {
                $httpClient = $httpClient->withOptions($sslOptions);
            }

            // Call Flowversal API
            // Author: Sanket - Enforce strict payload as per docs
            //Sanket v2.0 - use $this->model from config instead of hardcoded value
            $payload = [
                'messages' => $formattedMessages,
                'data' => [
                    'provider' => 'ollama',
                    'model' => $this->model
                ]
            ];

            $isFinalTurn = ($iterations === $maxIterations);

            if (!empty($toolDefinitions) && !$isFinalTurn) {
                $payload['tools'] = $toolDefinitions;
                $payload['tool_choice'] = 'auto'; // Author: Sanket - Force tool awareness
            }

            if ($isFinalTurn) {
                $formattedMessages[] = ['role' => 'system', 'content' => 'FINAL TURN: Summarize the results and explain to the user. DO NOT attempt to call any tools.'];
            }

            Log::info("Calling Flowversal API (Workpilot AI)", [
                'model' => 'qwen2.5:14b', 
                'url' => $this->baseUrl, 
                'turn' => $iterations . ($isFinalTurn ? ' (FINAL)' : ''),
                'payload' => $payload
            ]);
            //Sanket v2.0 - use SSL-aware HTTP client
            $apiClient = Http::timeout(300)->withToken($this->apiKey);
            if (!empty($sslOptions)) {
                $apiClient = $apiClient->withOptions($sslOptions);
            }
            $response = $apiClient->post($this->baseUrl, $payload);

            if ($response->failed()) {
                Log::error("Flowversal API Error", ['status' => $response->status(), 'body' => $response->body()]);
                throw new \Exception("Flowversal API Error: " . ($response->json('message') ?? $response->body()));
            }

            $rawBody = $response->body();
            Log::debug("Workpilot AI Raw SSE Response", ['body' => $rawBody]);
            $fullContent = "";
            $toolCalls = [];

            $lines = explode("\n", $rawBody);
            foreach ($lines as $line) {
                if (str_starts_with($line, "data: ")) {
                    $jsonStr = substr($line, 6);
                    
                    // Handle [DONE] signal
                    if (trim($jsonStr) === '[DONE]') {
                        continue;
                    }
                    
                    $data = json_decode($jsonStr, true);
                    
                    if (!$data) {
                        continue;
                    }
                    
                    if (isset($data['content'])) {
                        // Flowversal returns (accumulated) content directly - OVERWRITE buffer
                        $fullContent = $data['content'];
                    } elseif (isset($data['delta'])) {
                         // Fallback to delta if content is missing
                        $fullContent .= $data['delta'];
                    } elseif (($data['type'] ?? '') === 'error' || isset($data['error'])) {
                        // Handle API-side errors
                        $errorMessage = $data['error']['message'] ?? $data['message'] ?? 'AI service internal error';
                        Log::error("Workpilot AI Provider Error", ['error' => $errorMessage]);
                        $fullContent = "ERROR: " . $errorMessage;
                        break; // Stop parsing if error occurs
                    }
                    
                    if (isset($data['tool_calls'])) {
                        foreach ($data['tool_calls'] as $tc) {
                            $index = $tc['index'] ?? 0;
                            if (!isset($toolCalls[$index])) {
                                $toolCalls[$index] = $tc;
                            } else {
                                // Merge arguments if they come in chunks
                                if (isset($tc['function']['arguments'])) {
                                    $toolCalls[$index]['function']['arguments'] .= $tc['function']['arguments'];
                                }
                            }
                        }
                    }
                }
            }

            if (empty($toolCalls) && !empty($fullContent)) {
                // FALLBACK: Detect [TOOL_CALL: name|{args}] in text
                // Author: Sanket - Since proxy strips native tools
                if (preg_match_all('/\[TOOL_CALL:\s*([a-zA-Z0-9_-]+)\s*\|\s*(\{.*?\})\s*\]/s', $fullContent, $matches)) {
                    \Log::info("Workpilot AI Manual Tool Call Detected", ['matches' => $matches[1]]);
                    foreach ($matches[1] as $index => $name) {
                        $argsRaw = $matches[2][$index];
                        $args = json_decode($argsRaw, true) ?: [];
                        
                        // Author: Sanket - Aggressive manual loop prevention
                        $manualKey = $name . ':' . md5($argsRaw);
                        if (isset($executedTools[$manualKey])) {
                            Log::warning("Workpilot AI attempted repeated manual tool call: $name. Breaking loop.");
                            $fullContent = str_replace($matches[0][$index], "[DONE]", $fullContent); // Neutralize
                            continue;
                        }
                        $executedTools[$manualKey] = true;

                        \Log::info("Workpilot AI Executing Manual Tool: $name", ['args' => $args]);
                        
                        // Execute tool immediately
                        $output = ['error' => 'Tool not found'];
                        if (isset($this->tools[$name])) {
                            try {
                                $output = $this->tools[$name]->execute($args);
                                \Log::info("Workpilot AI Manual Tool Result", ['name' => $name, 'success' => !isset($output['error'])]);
                            } catch (\Exception $e) { 
                                $output = ['error' => $e->getMessage()];
                                \Log::error("Workpilot AI Manual Tool Error", ['name' => $name, 'error' => $e->getMessage()]);
                            }
                        }

                        // Replace marker with success note
                        $fullContent = str_replace($matches[0][$index], "[DONE: $name]", $fullContent);
                        
                        //Sanket v2.0 - use assistant+user pattern instead of system role for manual tool results
                        $formattedMessages[] = [
                            'role' => 'assistant',
                            'content' => "[DONE: $name]"
                        ];
                        $formattedMessages[] = [
                            'role' => 'user',
                            'content' => "Tool '$name' returned: " . json_encode($output) . ". Now provide the final response to the user. DO NOT call this tool again."
                        ];
                    }
                    $fullContent = trim($fullContent);
                    
                    // Allow up to 3 more turns if needed for complex follow-ups
                    $iterations = max($iterations, $maxIterations - 3); 
                    
                    $assistantMessage = ['role' => 'assistant', 'content' => $fullContent];
                    $formattedMessages[] = $assistantMessage;
                    continue; 
                }
            }

            $assistantMessage = [
                'role' => 'assistant',
                'content' => $fullContent,
            ];

            $formattedMessages[] = $assistantMessage;

            if ($isFinalTurn) {
                 // Author: Sanket - Softened cleanup: Only strip internal technical markers
                 // DO NOT strip [ ... ] if it looks like a list or JSON data the user should see
                 $fullContent = preg_replace('/SYSTEM:.*$/m', '', $fullContent);
                 $fullContent = preg_replace('/\[TOOL_CALL:.*?\]/s', '', $fullContent);
                 $fullContent = preg_replace('/\[DONE:.*?\]/s', '', $fullContent);
                 $fullContent = preg_replace('/\[DONE\]/s', '', $fullContent);
                 
                 return trim($fullContent) ?: "I have processed your request.";
            }

            // Check for tool calls
            if (empty($toolCalls)) {
                return $fullContent;
            }

            Log::info("Workpilot AI wants to use tools", ['count' => count($toolCalls)]);

            foreach ($toolCalls as $toolCall) {
                $functionName = $toolCall['function']['name'];
                $arguments = is_string($toolCall['function']['arguments']) 
                    ? json_decode($toolCall['function']['arguments'], true) 
                    : $toolCall['function']['arguments'];

                // Author: Sanket - Aggressive loop prevention
                // If the same tool function name has been called twice, it's a loop.
                $callCount = count(array_filter(array_keys($executedTools), fn($k) => str_starts_with($k, $functionName . ':')));
                if ($callCount >= 2) {
                    Log::warning("Workpilot AI attempted repeated tool call: $functionName. Breaking loop.");
                    return $fullContent ?: "I have processed your request.";
                }
                $executedTools[$functionName . ':' . md5(json_encode($arguments))] = true;

                Log::info("Workpilot AI Tool Call: $functionName", $arguments ?? []);

                $output = ['error' => 'Tool not found'];

                if (isset($this->tools[$functionName])) {
                    $tool = $this->tools[$functionName];
                    try {
                        $output = $tool->execute($arguments ?? []);
                    } catch (\Exception $e) {
                        $output = ['error' => $e->getMessage()];
                    }
                }

                //Sanket v2.0 - use assistant+system pattern for tool results so the model understands the flow correctly
                $formattedMessages[] = [
                    'role' => 'assistant',
                    'content' => "[DONE: $functionName]",
                ];
                $formattedMessages[] = [
                    'role' => 'user',
                    'content' => "Tool '$functionName' returned: " . json_encode($output) . ". Now provide the final response to the user. DO NOT call this tool again.",
                ];
            }
        }

        return "I've reached my thinking limit. Please try again or simplify your request.";
    }
}
