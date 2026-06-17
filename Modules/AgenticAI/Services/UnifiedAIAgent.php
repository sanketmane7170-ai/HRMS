<?php

namespace Modules\AgenticAI\Services;

use Illuminate\Support\Facades\Log;
use Modules\AgenticAI\Interfaces\ToolInterface;
use Modules\AgenticAI\Services\WorkpilotAIAgent;
use Modules\AgenticAI\Services\GeminiAgent;
use Modules\AgenticAI\Services\OpenAIAgent;

/**
 * UnifiedAIAgent - Intelligent AI Agent with Automatic Fallback
 * 
 * Author: Sanket
 * 
 * This service provides automatic failover between OpenAI (primary) and Gemini (backup).
 * When OpenAI fails due to rate limits, quota issues, or connectivity problems,
 * the system automatically switches to Gemini's FREE tier.
 */
class UnifiedAIAgent
{
    protected WorkpilotAIAgent $workpilotAIAgent; // Author: Sanket - Flowversal (Workpilot AI)
    protected GeminiAgent $geminiAgent; // Author: Sanket - Gemini (Backup)
    protected OpenAIAgent $openaiAgent; // Author: Sanket - OpenAI (New Primary)
    protected string $primaryProvider;
    protected ?int $conversationId = null;

    public function __construct()
    {
        // Initialize active agents - Workpilot AI + Gemini + OpenAI
        $this->workpilotAIAgent = new WorkpilotAIAgent(); // Author: Sanket
        $this->geminiAgent = new GeminiAgent(); // Author: Sanket
        $this->openaiAgent = new OpenAIAgent(); // Author: Sanket
        
        //Sanket v2.0 - use config() instead of env() so it works with cached config
        $this->primaryProvider = config('services.ai.primary_provider', 'openai'); 
        if ($this->primaryProvider === 'mom') {
            $this->primaryProvider = 'workpilot';
        }
    }

    /**
     * Register a tool with both agents
     * Author: Sanket
     */
    public function registerTool(ToolInterface $tool): self
    {
        $this->workpilotAIAgent->registerTool($tool);
        $this->geminiAgent->registerTool($tool);
        $this->openaiAgent->registerTool($tool);
        return $this;
    }

    /**
     * Set conversation ID for logging
     * Author: Sanket
     */
    public function setConversationId(int $id): self
    {
        $this->conversationId = $id;
        $this->workpilotAIAgent->setConversationId($id);
        $this->geminiAgent->setConversationId($id); //Sanket v2.0 - was missing, causing gemini logs to lose conversation context
        $this->openaiAgent->setConversationId($id);
        return $this;
    }

    /**
     * Main chat method with intelligent provider selection and automatic fallback
     * Author: Sanket
     */
    public function chat(array $messages, array $options = [])
    {
        Log::info("UnifiedAIAgent: Using provider: " . $this->primaryProvider, [
            'conversation_id' => $this->conversationId
        ]);

        //Sanket v2.0 - define provider order with fallback chain based on primary selection
        $providerChain = $this->getProviderChain();

        foreach ($providerChain as $index => $provider) {
            try {
                Log::info("UnifiedAIAgent: Attempting provider: {$provider['name']}", [
                    'attempt' => $index + 1,
                    'conversation_id' => $this->conversationId
                ]);
                return $provider['agent']->chat($messages, $options);
            } catch (\Exception $e) {
                Log::warning("UnifiedAIAgent: Provider {$provider['name']} failed", [
                    'error' => $e->getMessage(),
                    'conversation_id' => $this->conversationId
                ]);
                //Sanket v2.0 - if last provider in chain, throw the error
                if ($index === count($providerChain) - 1) {
                    throw $e;
                }
                Log::info("UnifiedAIAgent: Falling back to next provider");
            }
        }

        throw new \RuntimeException('All AI providers failed.');
    }

    //Sanket v2.0 - streaming chat: delegates to OpenAI primary (streaming not supported on Gemini/MomAI fallbacks)
    //Falls back to regular chat() if streaming fails for any reason
    public function streamChat(array $messages, callable $onToken, array $options = []): string
    {
        try {
            return $this->openaiAgent->streamChat($messages, $onToken, $options);
        } catch (\Exception $e) {
            Log::warning('UnifiedAIAgent::streamChat failed, falling back to regular chat', [
                'error' => $e->getMessage(),
                'conversation_id' => $this->conversationId,
            ]);
            //Sanket v2.0 - emit full response at once on fallback so caller still gets content
            $response = $this->chat($messages, $options);
            $onToken($response);
            return $response;
        }
    }

    /**
     * Get ordered provider chain based on primary provider setting
     * Author: Sanket v2.0
     */
    protected function getProviderChain(): array
    {
        $providers = [
            'openai' => ['name' => 'openai', 'agent' => $this->openaiAgent],
            'gemini' => ['name' => 'gemini', 'agent' => $this->geminiAgent],
            'workpilot' => ['name' => 'workpilot', 'agent' => $this->workpilotAIAgent],
        ];

        //Sanket v2.0 - primary goes first, rest follow as fallbacks; skip providers with missing API keys
        $primary = $providers[$this->primaryProvider] ?? $providers['openai'];
        unset($providers[$this->primaryProvider]);

        $chain = [$primary];
        foreach (array_values($providers) as $provider) {
            $chain[] = $provider;
        }

        // Filter out providers that have no API key configured
        return array_filter($chain, function ($provider) {
            try {
                $agent = $provider['agent'];
                $ref = new \ReflectionProperty($agent, 'apiKey');
                $ref->setAccessible(true);
                $key = $ref->getValue($agent);
                return !empty($key);
            } catch (\Exception $e) {
                return true; // If we can't check, keep it
            }
        });
    }




    /**
     * Generate a short 3-5 word title for a conversation
     * Author: Sanket
     */
    public function summarize(array $messages): string
    {
        $prompt = "Generate a very short, concise title (max 5 words) for this conversation based on the user's intent. Respond ONLY with the title. Do not use quotes or formatting. If you cannot determine a title, say 'HR Query'.\n\nConversation:\n";
        
        foreach ($messages as $msg) {
            $role = ($msg['role'] ?? ($msg['sender'] === 'user' ? 'user' : 'assistant'));
            $content = $msg['content'] ?? '';
            // Skip state markers in summarization context
            $cleanContent = preg_replace('/(🔍|⚙️|📋|⏳|✅|❌)\s*(UNDERSTANDING|PLANNING|EXECUTING|WAITING|COMPLETED|FAILED):?[^\n]*/i', '', $content);
            $prompt .= strtoupper($role) . ": " . trim($cleanContent) . "\n";
        }

        $summaryMessages = [
            ['role' => 'user', 'content' => $prompt]
        ];

        try {
            // Use low max tokens for efficiency
            $response = $this->chat($summaryMessages, ['max_tokens' => 30]);
            
            // Clean up common AI prefixes
            $title = trim($response, '" ');
            $title = preg_replace('/^(Title|Summary|Topic):\s*/i', '', $title);
            
            return $title ?: "HR Query";
        } catch (\Exception $e) {
            Log::error("Failed to generate smart title: " . $e->getMessage());
            return "HR Query";
        }
    }
}
