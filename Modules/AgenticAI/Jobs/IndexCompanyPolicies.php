<?php

namespace Modules\AgenticAI\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\PolicySetting\Entities\PolicySettings;
use Modules\AgenticAI\Services\VectorStore\DatabaseVectorStore;
use Modules\AgenticAI\Services\Ingestion\RecursiveChunker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IndexCompanyPolicies implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        // No args needed, we index all. Or could accept a specific Policy ID.
    }

    /**
     * Execute the job.
     */
    public function handle(DatabaseVectorStore $vectorStore, RecursiveChunker $chunker)
    {
        Log::info('Starting Policy Indexing Job...');

        // 1. Get all policies
        $policies = PolicySettings::all();
        $count = 0;

        foreach ($policies as $policy) {
            try {
                // Remove old chunks for this policy to prevent duplicates/ghosts
                // We use source_id + source_type for this.
                DB::table('ai_documents')
                    ->where('source_type', PolicySettings::class)
                    ->where('source_id', $policy->id)
                    ->delete();

                // 2. Prepare content
                $content = "Policy: {$policy->name}\nType: {$policy->type}\n\n{$policy->policy}";
                if ($policy->description) {
                    $content .= "\n\nDescription: {$policy->description}";
                }

                // 3. Chunk
                $chunks = $chunker->split($content);

                // 4. Upsert
                foreach ($chunks as $index => $chunkText) {
                    $metadata = [
                        'source_id' => $policy->id,
                        'source_type' => PolicySettings::class,
                        'title' => $policy->name,
                        'collection' => 'policies',
                        'chunk_index' => $index
                    ];

                    // Using upsertWithEmbedding directly
                    $vectorStore->upsertWithEmbedding($chunkText, $metadata);
                }
                
                $count++;
            } catch (\Exception $e) {
                Log::error("Failed to index policy ID {$policy->id}: " . $e->getMessage());
            }
        }

        Log::info("Helper Indexing Complete. Indexed {$count} policies.");
    }
}
