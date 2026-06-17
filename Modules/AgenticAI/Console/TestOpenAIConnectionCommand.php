<?php

namespace Modules\AgenticAI\Console;

use Illuminate\Console\Command;
use Modules\AgenticAI\Services\OpenAIAgent;
use Modules\AgenticAI\Services\ToolRegistryService;

class TestOpenAIConnectionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'agent:test-openai {prompt=Hello}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deep test connection and tools for OpenAI';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("=== Deep Testing OpenAI Pipeline ===");
        $this->info("Model: " . config('services.openai.model', 'gpt-4-turbo-preview'));
        $this->info("API Key: " . substr(config('services.openai.api_key'), 0, 8) . '...');
        $this->info("Prompt: " . $this->argument('prompt'));
        $this->line("------------------------------------");

        try {
            $agent = new OpenAIAgent();
            $registry = new ToolRegistryService();
            
            // For deep testing, we might want to register ALL tools if prompt is 'all'
            if ($this->argument('prompt') === 'debug_all') {
                $this->info("Mode: Debug All Available Tools");
                $tools = (new \Modules\AgenticAI\Tools\ToolRegistryService())->registerAllTools(); // Assuming this method exists or similar logic
                // If registerAllTools doesn't exist, we iterate manually or use the intent logic
            } else {
                $tools = $registry->getToolsForIntent($this->argument('prompt'));
            }
            
            foreach ($tools as $tool) {
                $agent->registerTool($tool);
                $this->comment("Registered tool: " . $tool->name());
            }

            $history = [['role' => 'user', 'content' => $this->argument('prompt')]];
            
            $start = microtime(true);
            $response = $agent->chat($history);
            $duration = round(microtime(true) - $start, 2);

            $this->info("Response ({$duration}s):");
            $this->line($response);
            
            if (str_contains($response, 'trouble connecting')) {
                $this->error("FAILED: The agent returned a fallback connection error.");
            } elseif (str_contains($response, 'rate limit') || str_contains($response, 'quota')) {
                $this->error("FAILED: Quota/Rate Limit still active.");
            } else {
                $this->info("SUCCESS: Full pipeline working correctly.");
            }

        } catch (\Exception $e) {
            $this->error("CRITICAL EXCEPTION: " . $e->getMessage());
            $this->error($e->getTraceAsString());
        }
    }
}
