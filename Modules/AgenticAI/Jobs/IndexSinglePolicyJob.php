<?php

namespace Modules\AgenticAI\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\PolicySetting\Entities\PolicySettings;
use Modules\AgenticAI\Services\VectorStore\DatabaseVectorStore;
use Modules\AgenticAI\Services\Ingestion\RecursiveChunker;

//Sanket v2.0 - indexes a single policy into the vector store (triggered by Observer)
class IndexSinglePolicyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(protected int $policyId) {}

    public function handle(DatabaseVectorStore $vectorStore, RecursiveChunker $chunker): void
    {
        $policy = PolicySettings::find($this->policyId);

        if (!$policy) {
            Log::warning("IndexSinglePolicyJob: Policy not found", ['id' => $this->policyId]);
            return;
        }

        try {
            //Sanket v2.0 - remove old chunks for this policy first
            DB::table('ai_documents')
                ->where('source_type', PolicySettings::class)
                ->where('source_id', $policy->id)
                ->delete();

            //Sanket v2.0 - prepare content with all relevant fields
            $content = "Policy: {$policy->name}\nType: {$policy->type}\n\n{$policy->policy}";
            if ($policy->description) {
                $content .= "\n\nDescription: {$policy->description}";
            }

            //Sanket v2.0 - chunk and index with embeddings
            $chunks = $chunker->split($content);

            foreach ($chunks as $index => $chunkText) {
                $metadata = [
                    'source_id' => $policy->id,
                    'source_type' => PolicySettings::class,
                    'title' => $policy->name,
                    'collection' => 'policies',
                    'chunk_index' => $index,
                ];

                $vectorStore->upsertWithEmbedding($chunkText, $metadata);
            }

            Log::info("IndexSinglePolicyJob: Indexed policy successfully", [
                'policy_id' => $policy->id,
                'chunks' => count($chunks),
            ]);
        } catch (\Exception $e) {
            Log::error("IndexSinglePolicyJob: Failed to index policy", [
                'error' => $e->getMessage(),
                'policy_id' => $policy->id,
            ]);
            throw $e; // Retry
        }
    }
}
