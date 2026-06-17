<?php

namespace Modules\AgenticAI\Services;

use Modules\AgenticAI\Interfaces\VectorStoreInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PineconeService implements VectorStoreInterface
{
    protected string $apiKey;
    protected string $indexName;
    protected ?string $host = null;

    public function __construct()
    {
        $this->apiKey = env('PINECONE_API_KEY');
        $this->indexName = 'mom-digital-hr';
    }

    protected function getHost(): ?string
    {
        if ($this->host) return $this->host;

        // Fetch index details to get the host
        $response = Http::withHeaders([
            'Api-Key' => $this->apiKey,
        ])->get("https://api.pinecone.io/indexes/{$this->indexName}");

        if ($response->successful()) {
            $this->host = "https://" . $response->json('host');
            return $this->host;
        }

        Log::error("Pinecone Host Error: " . $response->body());
        return null;
    }

    public function search(string $queryText, int $limit = 5): array
    {
        $host = $this->getHost();
        if (!$host) return [];

        $embedding = $this->getEmbedding($queryText);
        if (!$embedding) return [];

        $response = Http::withHeaders([
            'Api-Key' => $this->apiKey,
        ])->post("{$host}/vectors/query", [
            'vector' => $embedding,
            'topK' => $limit,
            'includeMetadata' => true,
        ]);

        return $response->json('matches') ?? [];
    }

    public function upsert(string $id, string $text, array $metadata = []): void
    {
        $host = $this->getHost();
        if (!$host) {
            Log::error("Pinecone Host Not Found. Cannot Upsert.");
            return;
        }

        $embedding = $this->getEmbedding($text);

        if ($embedding) {
            $payload = [
                'vectors' => [
                    [
                        'id' => $id,
                        'values' => $embedding,
                        'metadata' => array_merge($metadata, ['text' => $text]),
                    ]
                ]
            ];

            $response = Http::withHeaders([
                'Api-Key' => $this->apiKey,
            ])->post("{$host}/vectors/upsert", $payload);

            if (!$response->successful()) {
                Log::error("Pinecone Upsert Failed: " . $response->body());
            }
        }
    }

    protected function getEmbedding(string $text): ?array
    {
        try {
            $client = \OpenAI::client(env('OPENAI_API_KEY'));
            $response = $client->embeddings()->create([
                'model' => 'text-embedding-ada-002',
                'input' => $text,
            ]);

            return $response->embeddings[0]->embedding;
        } catch (\Exception $e) {
            Log::error("Embedding Error using OpenAI: " . $e->getMessage());
            return null;
        }
    }
}
