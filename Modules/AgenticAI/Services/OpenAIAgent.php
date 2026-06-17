<?php

namespace Modules\AgenticAI\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\AgenticAI\Interfaces\ToolInterface;
use Modules\AgenticAI\Traits\ResolvesSSLCertificate;

/**
 * OpenAIAgent - Direct implementation for OpenAI
 */
class OpenAIAgent
{
    use ResolvesSSLCertificate;
    protected ?string $apiKey = '';
    protected ?string $model = '';
    protected ?string $baseUrl = 'https://api.openai.com/v1/chat/completions';
    protected array $tools = [];
    protected array $systemInstructions = [];
    protected ?int $conversationId = null;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key') ?? '';
        //Sanket v2.0 - default to gpt-4o-mini (3-5x faster than gpt-4o, sufficient for HR queries)
        $this->model = config('services.openai.model', 'gpt-4o-mini') ?? 'gpt-4o-mini';
        
        // Get authenticated user info for context
        $user = auth()->user();
        $now = now();
        $userContext = $user ? "You are assisting {$user->name} (Employee ID: {$user->id}, Email: {$user->email})." : "You are assisting a user.";
        $timeContext = "Current Server Time: " . $now->format('l, F j, Y, g:i A') . " (Timezone: " . config('app.timezone') . ")";
        
        //Sanket v2.0 - system prompt with ReAct reasoning pattern and schema awareness
        $this->systemInstructions = [
            "You are Workpilot AI, an intelligent agentic HR assistant with full system access.",
            $userContext,
            $timeContext,
            "LANGUAGE: Always respond in ENGLISH unless the user speaks another language.",
            
            "=== REASONING PATTERN (ReAct) ===",
            "For complex questions, think step-by-step:",
            "1. THOUGHT: Understand what the user needs",
            "2. ACTION: Call the right tool(s) to get data",
            "3. OBSERVATION: Analyze tool results",
            "4. ANSWER: Respond clearly with the final answer",
            "If a tool returns an error or wrong columns, SELF-CORRECT: analyze the error message, check the schema, and retry with a fixed query.",
            
            "=== CORE RULES ===",
            "1. BE PROACTIVE: Call tools immediately. Never ask 'Do you want me to check?' — just check.",
            "2. BE CONCISE: Provide answers, not explanations of your process.",
            "3. NO HALLUCINATION: ALWAYS use tools to fetch real data. Never fabricate numbers.",
            "4. GENERAL KNOWLEDGE: For greetings, world knowledge, or general HR/legal questions, answer directly using your training data.",
            "5. TOOL RESULTS: Present data using Markdown tables when there are multiple rows.",
            "6. ERROR HANDLING: If a tool fails, report honestly. If a SQL query fails, read the error, fix the query, and retry.",
            "7. KNOWLEDGE FALLBACK: If policy/knowledge search returns NO results, answer from general knowledge. Clearly state it's general knowledge, not company policy.",
            "8. DATABASE EXPLORER: You have explore_database with full schema in your context below. Write SELECT queries directly — you already know all table names and columns. Always use LIMIT.",
            
            "=== YOUR AVAILABLE TOOLS ===",
            "%DYNAMIC_CAPABILITIES%",
            
            "%SCHEMA_CONTEXT%",
        ];
    }

    //Sanket v2.0 - check if the latest user message likely needs DB schema context
    //Returns false for greetings/general chat to skip injecting 77K chars of schema on every message
    protected function needsSchemaContext(array $messages): bool
    {
        $lastUserMsg = '';
        foreach (array_reverse($messages) as $msg) {
            if (($msg['role'] ?? '') === 'user') {
                $lastUserMsg = trim(strtolower($msg['content'] ?? ''));
                break;
            }
        }

        if (empty($lastUserMsg)) {
            return true; // unknown message type, inject schema to be safe
        }

        //Sanket v2.0 - short greetings and general phrases that never need DB access
        $noSchemaPatterns = [
            '/^(hi|hello|hey|hiya|howdy)\b/i',
            '/^good\s*(morning|afternoon|evening|night|day)\b/i',
            '/^(how are you|how do you do|what\'s up|sup|yo)\b/i',
            '/^(thanks|thank you|thx|ty|cheers|ok|okay|got it|sure|great|noted|understood|alright|fine|no problem)\b/i',
            '/^(bye|goodbye|see you|cya|take care|ttyl|later)\b/i',
            '/^(who are you|what are you|what can you do|introduce yourself|help me|what is your name)\b/i',
        ];

        foreach ($noSchemaPatterns as $pattern) {
            if (preg_match($pattern, $lastUserMsg)) {
                return false;
            }
        }

        return true; // inject schema for all substantive HR queries
    }

    //Sanket v2.0 - streaming chat: executes tool calls synchronously, then streams final text tokens
    //Uses openai-php/client createStreamed() so the user sees tokens arrive in real time
    public function streamChat(array $messages, callable $onToken, array $options = []): string
    {
        set_time_limit(300);

        $capabilities  = $this->buildCapabilitiesList();
        $schemaContext = '';
        try {
            $schemaProvider = new SchemaContextProvider();
            if ($this->needsSchemaContext($messages)) {
                $schemaContext = $schemaProvider->getSchemaContext() . "\n" . $schemaProvider->getRelationshipMap();
            } else {
                $schemaContext = $schemaProvider->getTableNamesOnly();
            }
        } catch (\Exception $e) {
            Log::warning('OpenAIAgent::streamChat schema failed', ['error' => $e->getMessage()]);
        }

        $systemPrompt = implode("\n", array_map(function ($line) use ($capabilities, $schemaContext) {
            return str_replace(['%DYNAMIC_CAPABILITIES%', '%SCHEMA_CONTEXT%'], [$capabilities, $schemaContext], $line);
        }, $this->systemInstructions));

        $formattedMessages = [['role' => 'system', 'content' => $systemPrompt]];
        foreach ($messages as $msg) {
            $formattedMessages[] = ['role' => $msg['role'], 'content' => $msg['content'] ?? ''];
        }

        $toolDefinitions = [];
        foreach ($this->tools as $tool) {
            $toolDefinitions[] = [
                'type'     => 'function',
                'function' => ['name' => $tool->name(), 'description' => $tool->description(), 'parameters' => $tool->schema()],
            ];
        }

        //Sanket v2.0 - use openai-php/client for streaming; php-http/guzzle7-adapter provides PSR-18 transport
        $client = \OpenAI::client($this->apiKey);
        $iterations    = 0;
        $maxIterations = 5;
        $executedTools = [];
        $fullResponse  = '';

        while ($iterations < $maxIterations) {
            $iterations++;
            $isFinalTurn = ($iterations === $maxIterations);

            $payload = ['model' => $this->model, 'messages' => $formattedMessages];

            if (!empty($options['max_tokens'])) {
                $payload['max_tokens'] = (int) $options['max_tokens'];
            }

            if (!empty($toolDefinitions) && !$isFinalTurn) {
                $payload['tools']       = $toolDefinitions;
                $payload['tool_choice'] = 'auto';
            }

            if ($isFinalTurn) {
                $formattedMessages[] = ['role' => 'system', 'content' => 'FINAL TURN: Summarize and respond. DO NOT attempt more tool calls.'];
                $payload['messages']  = $formattedMessages;
            }

            Log::info('OpenAIAgent::streamChat API call', [
                'model' => $this->model, 'turn' => $iterations, 'conversation_id' => $this->conversationId,
            ]);

            //Sanket v2.0 - use streaming API call; accumulate tool_call deltas, stream content tokens immediately
            $stream       = $client->chat()->createStreamed($payload);
            $content      = '';
            $toolCallsAcc = []; // Accumulated tool call fragments keyed by index

            foreach ($stream as $response) {
                $choice      = $response->choices[0] ?? null;
                if (!$choice) continue;
                $delta       = $choice->delta;

                //Sanket v2.0 - stream text tokens to caller immediately as they arrive
                if ($delta->content !== null) {
                    $content      .= $delta->content;
                    $fullResponse .= $delta->content;
                    $onToken($delta->content);
                }

                //Sanket v2.0 - accumulate tool call fragments (each chunk adds partial arguments)
                if (!empty($delta->toolCalls)) {
                    foreach ($delta->toolCalls as $tcDelta) {
                        $idx = $tcDelta->index;
                        if (!isset($toolCallsAcc[$idx])) {
                            $toolCallsAcc[$idx] = ['id' => '', 'name' => '', 'arguments' => ''];
                        }
                        if ($tcDelta->id)                      $toolCallsAcc[$idx]['id'] = $tcDelta->id;
                        if ($tcDelta->function->name ?? null)  $toolCallsAcc[$idx]['name'] .= $tcDelta->function->name;
                        if ($tcDelta->function->arguments ?? null) $toolCallsAcc[$idx]['arguments'] .= $tcDelta->function->arguments;
                    }
                }
            }

            //Sanket v2.0 - if no tool calls were requested, streaming is complete
            if (empty($toolCallsAcc)) {
                return $fullResponse;
            }

            //Sanket v2.0 - build assistant tool-call message and execute each tool
            $assistantMsg = ['role' => 'assistant', 'content' => $content ?: null, 'tool_calls' => []];
            foreach ($toolCallsAcc as $tc) {
                $assistantMsg['tool_calls'][] = [
                    'id' => $tc['id'], 'type' => 'function',
                    'function' => ['name' => $tc['name'], 'arguments' => $tc['arguments']],
                ];
            }
            $formattedMessages[] = $assistantMsg;

            foreach ($toolCallsAcc as $tc) {
                $callKey = $tc['name'] . ':' . md5($tc['arguments']);
                if (isset($executedTools[$callKey])) {
                    $formattedMessages[] = ['tool_call_id' => $tc['id'], 'role' => 'tool', 'name' => $tc['name'], 'content' => json_encode(['error' => 'Already called.'])];
                    continue;
                }
                $executedTools[$callKey] = true;
                $arguments = json_decode($tc['arguments'], true) ?: [];
                $output    = isset($this->tools[$tc['name']]) ? $this->tools[$tc['name']]->execute($arguments) : ['error' => "Tool '{$tc['name']}' not found."];
                //Sanket v2.0 - truncate large tool results to prevent oversized payloads timing out on turn 2
                $output = $this->truncateToolOutput($output);
                $formattedMessages[] = ['tool_call_id' => $tc['id'], 'role' => 'tool', 'name' => $tc['name'], 'content' => json_encode($output)];
            }
        }

        return $fullResponse ?: 'I have processed your request.';
    }

    //Sanket v2.0 - caps tool output at 20 rows / 6000 chars to prevent context window timeouts on turn 2
    protected function truncateToolOutput(mixed $output): mixed
    {
        if (!is_array($output)) {
            return $output;
        }
        // If the array has a 'data' key with many rows, slice it
        if (isset($output['data']) && is_array($output['data']) && count($output['data']) > 20) {
            $total = count($output['data']);
            $output['data'] = array_values(array_slice($output['data'], 0, 20));
            $output['_truncated'] = true;
            $output['_note'] = "Results limited to 20 of {$total} rows to fit context window.";
            return $output;
        }
        // If it's a flat list of rows, slice it
        if (array_is_list($output) && count($output) > 20) {
            $total = count($output);
            return [
                'data'       => array_values(array_slice($output, 0, 20)),
                '_truncated' => true,
                '_note'      => "Results limited to 20 of {$total} rows to fit context window.",
            ];
        }
        // Final safety: cap raw JSON size at 6000 chars
        if (strlen(json_encode($output)) > 6000) {
            return ['_truncated' => true, '_note' => 'Result too large to include. Please narrow your query.'];
        }
        return $output;
    }

    protected function buildCapabilitiesList(): string
    {
        if (empty($this->tools)) {
            return "No specialized tools registered.";
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

    public function chat(array $messages, array $options = [])
    {
        try {
            return $this->performChat($messages, $options);
        } catch (\Exception $e) {
            Log::error('OpenAI API failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function performChat(array $messages, array $options = [])
    {
        set_time_limit(300);
        
        $capabilities = $this->buildCapabilitiesList();
        
        //Sanket v2.0 - only inject full DB schema when query likely needs database access
        //Simple greetings and general questions skip the 77K schema to save tokens and speed up response
        $schemaContext = '';
        try {
            $schemaProvider = new SchemaContextProvider();
            if ($this->needsSchemaContext($messages)) {
                $schemaContext = $schemaProvider->getSchemaContext() . "\n" . $schemaProvider->getRelationshipMap();
            } else {
                //Sanket v2.0 - still inject table names (~500 chars) so AI knows what data is available
                $schemaContext = $schemaProvider->getTableNamesOnly();
            }
        } catch (\Exception $e) {
            Log::warning('OpenAIAgent: Schema context injection failed', ['error' => $e->getMessage()]);
        }
        
        $systemPrompt = implode("\n", array_map(function($line) use ($capabilities, $schemaContext) {
            $line = str_replace('%DYNAMIC_CAPABILITIES%', $capabilities, $line);
            $line = str_replace('%SCHEMA_CONTEXT%', $schemaContext, $line);
            return $line;
        }, $this->systemInstructions));

        $formattedMessages = [
            ['role' => 'system', 'content' => $systemPrompt]
        ];

        //Sanket v2.0 - only pass role+content to OpenAI, strip metadata and any extra fields that cause API errors
        foreach ($messages as $msg) {
            $formattedMessages[] = [
                'role' => $msg['role'],
                'content' => $msg['content'] ?? ''
            ];
        }

        // Prepare tools
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
        //Sanket v2.0 - 5 iterations is enough for multi-step chains; 10 caused latency on every message
        $maxIterations = 5;
        $executedTools = []; //Sanket v2.0 - track executed tools to prevent infinite loops

        while ($iterations < $maxIterations) {
            $iterations++;
            $isFinalTurn = ($iterations === $maxIterations);
            
            $payload = [
                'model' => $this->model,
                'messages' => $formattedMessages,
            ];

            //Sanket v2.0 - apply caller-supplied options (e.g. max_tokens) to the API payload
            if (!empty($options['max_tokens'])) {
                $payload['max_tokens'] = (int) $options['max_tokens'];
            }

            //Sanket v2.0 - on final turn, don't offer tools so AI is forced to summarize
            if (!empty($toolDefinitions) && !$isFinalTurn) {
                $payload['tools'] = $toolDefinitions;
                $payload['tool_choice'] = 'auto';
            }

            if ($isFinalTurn) {
                $formattedMessages[] = ['role' => 'system', 'content' => 'FINAL TURN: Summarize the results and respond to the user. DO NOT attempt any more tool calls.'];
                $payload['messages'] = $formattedMessages;
            }

            Log::info("Calling OpenAI API", [
                'model' => $this->model, 
                'turn' => $iterations . ($isFinalTurn ? ' (FINAL)' : ''),
                'conversation_id' => $this->conversationId,
                'tools_count' => count($toolDefinitions)
            ]);

            //Sanket v2.0 - ensure SSL cert bundle is found (fixes cURL error 60 on Docker/Windows)
            //Sanket v2.0 - 120s timeout: large tool results (e.g. 50 employee rows) cause slow second API turn
            $httpClient = Http::timeout(120)
                ->withToken($this->apiKey);
            
            $certPath = $this->getCertPath();
            if ($certPath) {
                $httpClient = $httpClient->withOptions(['verify' => $certPath]);
            }

            //Sanket v2.0 - retry with backoff on rate limit (429) errors
            $maxRetries = 2;
            $response = null;
            for ($retry = 0; $retry <= $maxRetries; $retry++) {
                $response = $httpClient->post($this->baseUrl, $payload);

                if ($response->status() === 429 && $retry < $maxRetries) {
                    //Sanket v2.0 - parse retry-after from header or error message, cap at 30s
                    $retryAfter = (int) ($response->header('Retry-After') ?? 0);
                    if ($retryAfter <= 0) {
                        preg_match('/try again in ([\d.]+)s/', $response->json('error.message') ?? '', $m);
                        $retryAfter = isset($m[1]) ? (int) ceil((float) $m[1]) : (5 * ($retry + 1));
                    }
                    $retryAfter = min($retryAfter, 30);
                    Log::warning("OpenAI rate limited, retrying in {$retryAfter}s", [
                        'attempt' => $retry + 1,
                        'conversation_id' => $this->conversationId
                    ]);
                    sleep($retryAfter);
                    continue;
                }
                break;
            }

            if ($response->failed()) {
                Log::error("OpenAI API Error", [
                    'status' => $response->status(), 
                    'body' => $response->body(),
                    'conversation_id' => $this->conversationId
                ]);
                throw new \Exception("OpenAI API Error: " . ($response->json('error.message') ?? $response->body()));
            }

            $data = $response->json();
            $assistantMessage = $data['choices'][0]['message'];
            
            $formattedMessages[] = $assistantMessage;

            //Sanket v2.0 - if no tool calls, return the text response
            if (empty($assistantMessage['tool_calls'])) {
                return $assistantMessage['content'] ?? 'I have processed your request.';
            }

            Log::info("OpenAI wants to use tools", [
                'count' => count($assistantMessage['tool_calls']),
                'conversation_id' => $this->conversationId
            ]);

            foreach ($assistantMessage['tool_calls'] as $toolCall) {
                $functionName = $toolCall['function']['name'];
                $arguments = json_decode($toolCall['function']['arguments'], true) ?: [];

                //Sanket v2.0 - loop prevention: if same tool+args called twice, break the cycle
                $callKey = $functionName . ':' . md5(json_encode($arguments));
                if (isset($executedTools[$callKey])) {
                    Log::warning("OpenAI repeated tool call detected, breaking loop", [
                        'tool' => $functionName,
                        'conversation_id' => $this->conversationId
                    ]);
                    $formattedMessages[] = [
                        'tool_call_id' => $toolCall['id'],
                        'role' => 'tool',
                        'name' => $functionName,
                        'content' => json_encode(['error' => 'This tool was already called with the same arguments. Use the previous result.']),
                    ];
                    continue;
                }
                $executedTools[$callKey] = true;

                Log::info("OpenAI Tool Call: $functionName", [
                    'args' => $arguments,
                    'conversation_id' => $this->conversationId
                ]);

                $output = ['error' => "Tool '$functionName' not found. Available tools: " . implode(', ', array_keys($this->tools))];
                if (isset($this->tools[$functionName])) {
                    try {
                        $output = $this->tools[$functionName]->execute($arguments);
                        Log::info("OpenAI Tool Result: $functionName", [
                            'success' => !isset($output['error']),
                            'conversation_id' => $this->conversationId
                        ]);
                    } catch (\Exception $e) {
                        $output = ['error' => $e->getMessage()];
                        Log::error("OpenAI Tool Error: $functionName", [
                            'error' => $e->getMessage(),
                            'conversation_id' => $this->conversationId
                        ]);
                    }
                } else {
                    Log::warning("OpenAI called unknown tool: $functionName", [
                        'available' => array_keys($this->tools),
                        'conversation_id' => $this->conversationId
                    ]);
                }

                //Sanket v2.0 - truncate large tool results to prevent oversized payloads timing out on turn 2
                $output = $this->truncateToolOutput($output);
                $formattedMessages[] = [
                    'tool_call_id' => $toolCall['id'],
                    'role' => 'tool',
                    'name' => $functionName,
                    'content' => json_encode($output),
                ];
            }
        }

        return "I've reached my thinking limit. Please try again.";
    }
}
