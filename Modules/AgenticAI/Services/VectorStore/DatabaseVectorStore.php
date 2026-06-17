<?php

namespace Modules\AgenticAI\Services\VectorStore;

use Modules\AgenticAI\Interfaces\VectorStoreInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DatabaseVectorStore implements VectorStoreInterface
{
    protected string $table = 'ai_documents';


    
    // We need to inject Embedding service to actually generate the vector before saving.
    protected $embeddingService;
    
    public function __construct()
    {
        // Manual instantiation or Dependency Injection. 
        // Ideally DI, but for now we'll new it up or grab from container.
        $this->embeddingService = app(\Modules\AgenticAI\Services\Embedding\OpenAIEmbeddingService::class);
    }
    
    public function upsertWithEmbedding(string $text, array $metadata = []): void
    {
        $vector = $this->embeddingService->embed($text);
        
        DB::table($this->table)->insert([
            'collection' => $metadata['collection'] ?? 'default',
            'content_chunk' => $text,
            'embedding' => json_encode($vector),
            'metadata' => json_encode($metadata),
            'source_type' => $metadata['source_type'] ?? null,
            'source_id' => $metadata['source_id'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    
    // Interface implementation
    public function upsert(string $id, string $text, array $metadata = []): void
    {
        $this->upsertWithEmbedding($text, $metadata);
    }

    public function search(string $queryText, int $limit = 5): array
    {
        $queryVector = $this->embeddingService->embed($queryText);
        
        // Fetch all documents (naive approach) or filter by collection if possible
        // For < 5000 docs, fetching ID + Vector is fast enough.
        // We select minimal data.
        $documents = DB::table($this->table)
            ->select('id', 'content_chunk', 'embedding', 'metadata')
            ->get();
            
        $results = [];
        
        foreach ($documents as $doc) {
            $docVector = json_decode($doc->embedding, true);
            if (!$docVector) continue;
            
            $similarity = $this->cosineSimilarity($queryVector, $docVector);
            
            if ($similarity > 0.3) { // Threshold
                 $results[] = [
                     'id' => $doc->id,
                     'content' => $doc->content_chunk,
                     'metadata' => json_decode($doc->metadata, true),
                     'score' => $similarity
                 ];
            }
        }
        
        // Sort by score DESC
        usort($results, fn($a, $b) => $b['score'] <=> $a['score']);
        
        return array_slice($results, 0, $limit);
    }
    
    private function cosineSimilarity(array $vecA, array $vecB): float
    {
        $dotProduct = 0;
        $normA = 0;
        $normB = 0;
        
        // Vectors should be same length (1536 for text-embedding-3-small)
        $count = count($vecA);
        
        for ($i = 0; $i < $count; $i++) {
            $valA = $vecA[$i];
            $valB = $vecB[$i] ?? 0; // Handle partials if any
            
            $dotProduct += $valA * $valB;
            $normA += $valA * $valA;
            $normB += $valB * $valB;
        }
        
        if ($normA == 0 || $normB == 0) return 0;
        
        return $dotProduct / (sqrt($normA) * sqrt($normB));
    }
}
