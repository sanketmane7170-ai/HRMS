<?php

namespace Modules\AgenticAI\Console;

use Illuminate\Console\Command;
use Modules\AgenticAI\Services\GeminiAgent;

class TestGeminiConnectionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'agent:test-gemini {prompt=Hello}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test connection to Gemini API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Testing Gemini Connection...");
        $this->info("Model: " . config('services.gemini.model'));
        $this->info("API Key: " . substr(config('services.gemini.api_key'), 0, 5) . '...');
        $this->info("Prompt: " . $this->argument('prompt'));

        try {
            $agent = new GeminiAgent();
            $registry = new \Modules\AgenticAI\Services\ToolRegistryService();
            $tools = $registry->getToolsForIntent($this->argument('prompt'));
            
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
                $this->error("FAILED: The agent returned the fallback error message.");
            } else {
                $this->info("SUCCESS: Connection working.");
            }

        } catch (\Exception $e) {
            $this->error("CRITICAL EXCEPTION: " . $e->getMessage());
            $this->error($e->getTraceAsString());
        }
    }
}
