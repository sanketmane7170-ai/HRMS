<?php

namespace Modules\AgenticAI\Services\Embedding;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use OpenAI\Client;

class OpenAIEmbeddingService
{
    protected ?Client $client = null;
    protected string $model = 'text-embedding-3-small';

    public function __construct()
    {
        $apiKey = config('services.openai.api_key');
        if ($apiKey) {
            $this->client = \OpenAI::client($apiKey);
        }
    }

    /**
     * Generate embedding for a text string.
     * Checks cache first to save costs.
     */
    public function embed(string $text): array
    {
        if (!$this->client) {
            Log::warning("Embedding skipped: OpenAI client not initialized (check API key).");
            return array_fill(0, 1536, 0); // Return zero vector to prevent further crashes
        }

        // cleanup text
        $text = str_replace("\n", " ", $text);
        
        // Cache key based on MD5 of text + model
        $cacheKey = 'embedding_' . md5($text . $this->model);

        return Cache::rememberForever($cacheKey, function () use ($text) {
            try {
                $response = $this->client->embeddings()->create([
                    'model' => $this->model,
                    'input' => $text,
                ]);

                return $response->embeddings[0]->embedding;
            } catch (\Exception $e) {
                Log::error("Embedding failed: " . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Batch embed texts.
     */
    public function batchEmbed(array $texts): array
    {
        // Simple implementation: Loop. 
        // In real massive scale, use batch API, but for caching simplicity loop is fine for <100 items.
        $embeddings = [];
        foreach ($texts as $text) {
            $embeddings[] = $this->embed($text);
        }
        return $embeddings;
    }
}
