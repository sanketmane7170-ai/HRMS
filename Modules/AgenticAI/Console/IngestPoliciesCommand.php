<?php

namespace Modules\AgenticAI\Console;

use Illuminate\Console\Command;
use App\Models\CompanyPolicy;
use Modules\AgenticAI\Services\PineconeService;
use Illuminate\Support\Str;

class IngestPoliciesCommand extends Command
{
    protected $signature = 'agent:ingest-policies';
    protected $description = 'Ingest Company Policies into Pinecone Vector DB';

    public function handle()
    {
        $this->info("Starting Policy Ingestion...");

        $policies = CompanyPolicy::all();
        $pinecone = new PineconeService();

        $bar = $this->output->createProgressBar(count($policies));
        $bar->start();

        foreach ($policies as $policy) {
            // Create a rich text representation
            $content = "Policy Title: " . $policy->title . "\n";
            $content .= "Content: " . strip_tags($policy->description); // Simple HTML strip
            
            // Unique ID for Vector DB
            $id = "policy_" . $policy->id;

            $pinecone->upsert($id, $content, [
                'type' => 'policy',
                'title' => $policy->title,
                'branch_id' => $policy->branch_id ?? 'all',
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Ingestion Complete!");
    }
}
