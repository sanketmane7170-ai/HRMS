<?php

namespace Modules\AgenticAI\Console;

use Illuminate\Console\Command;
use Modules\AgenticAI\Services\SchemaContextProvider;
use Modules\AgenticAI\Services\SemanticIntentRouter;
use Modules\AgenticAI\Jobs\IndexCompanyPolicies;

//Sanket v2.0 - warmup command to pre-compute all caches so the first user request is fast
class WarmupAICommand extends Command
{
    protected $signature = 'agentic:warmup';
    protected $description = 'Pre-compute schema cache, semantic router embeddings, and index policies into vector store';

    public function handle(): void
    {
        $this->info('=== Workpilot AI Warmup ===');

        // 1. Schema cache
        $this->info('[1/3] Caching database schema...');
        try {
            $schema = new SchemaContextProvider();
            $schema->invalidate(); // Clear old cache
            $context = $schema->getSchemaContext();
            $relations = $schema->getRelationshipMap();
            $this->info('  Schema cached (' . strlen($context) . ' chars, ' . strlen($relations) . ' chars relationships)');
        } catch (\Exception $e) {
            $this->error('  Schema cache failed: ' . $e->getMessage());
        }

        // 2. Semantic router embeddings
        $this->info('[2/3] Computing semantic intent embeddings (33 categories)...');
        try {
            \Illuminate\Support\Facades\Cache::forget('ai_category_embeddings');
            $router = new SemanticIntentRouter();
            $categories = $router->classifyIntent('test warmup query', 1);
            $this->info('  Embeddings computed and cached');
        } catch (\Exception $e) {
            $this->error('  Embedding computation failed: ' . $e->getMessage());
            $this->warn('  This is OK if OpenAI key is not set — keyword routing will be used as fallback');
        }

        // 3. Index policies
        $this->info('[3/3] Indexing company policies into vector store...');
        try {
            IndexCompanyPolicies::dispatchSync();
            $count = \Illuminate\Support\Facades\DB::table('ai_documents')->count();
            $this->info("  Indexed — {$count} document chunks in vector store");
        } catch (\Exception $e) {
            $this->error('  Policy indexing failed: ' . $e->getMessage());
        }

        $this->newLine();
        $this->info('Warmup complete. Workpilot AI is ready.');
    }
}
