<?php

namespace Modules\AgenticAI\Services\Embedding;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * GeminiEmbeddingService - FREE Embedding Service using Google Gemini
 * 
 * Author: Sanket
 * 
 * This service provides text embeddings using Google's FREE Gemini API.
 * Uses text-embedding-004 model which is completely free with generous rate limits.
 */
class GeminiEmbeddingService
{
    protected string $apiKey;
    protected string $model = 'text-embedding-004';
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
    }

    /**
     * Generate embedding for a text string
     * Author: Sanket
     * 
     * Checks cache first to save API calls and improve performance.
     */
    public function embed(string $text): array
    {
        // Cleanup text
        $text = str_replace("\n", " ", $text);
        
        // Cache key based on MD5 of text + model
        $cacheKey = 'embedding_gemini_' . md5($text . $this->model);

        return Cache::rememberForever($cacheKey, function () use ($text) {
            try {
                $response = Http::timeout(30)->post(
                    "{$this->baseUrl}{$this->model}:embedContent?key={$this->apiKey}",
                    [
                        'model' => "models/{$this->model}",
                        'content' => [
                            'parts' => [['text' => $text]]
                        ]
                    ]
                );

                if ($response->failed()) {
                    $error = $response->json();
                    
                    // Check for quota errors
                    if (isset($error['error']['code']) && $error['error']['code'] == 429) {
                        throw new \Exception("Gemini Embedding API quota exceeded. Please check your Google AI Studio quota.");
                    }
                    
                    throw new \Exception("Gemini Embedding API Error: " . $response->body());
                }

                $data = $response->json();
                
                if (!isset($data['embedding']['values'])) {
                    throw new \Exception("Invalid response from Gemini Embedding API: " . json_encode($data));
                }

                return $data['embedding']['values'];

            } catch (\Exception $e) {
                Log::error("Gemini Embedding failed", [
                    'error' => $e->getMessage(),
                    'text_length' => strlen($text)
                ]);
                throw $e;
            }
        });
    }

    /**
     * Batch embed multiple texts
     * Author: Sanket
     * 
     * For massive scale, consider using batch API.
     * For <100 items, loop with caching is efficient.
     */
    public function batchEmbed(array $texts): array
    {
        $embeddings = [];
        foreach ($texts as $text) {
            $embeddings[] = $this->embed($text);
        }
        return $embeddings;
    }

    /**
     * Clear embedding cache for a specific text
     * Author: Sanket
     */
    public function clearCache(string $text): void
    {
        $text = str_replace("\n", " ", $text);
        $cacheKey = 'embedding_gemini_' . md5($text . $this->model);
        Cache::forget($cacheKey);
    }

    /**
     * Clear all Gemini embedding cache
     * Author: Sanket
     */
    public function clearAllCache(): void
    {
        // This is a simple implementation
        // For production, consider using cache tags if your cache driver supports it
        Log::info("Clearing all Gemini embedding cache");
        Cache::flush();
    }
}
