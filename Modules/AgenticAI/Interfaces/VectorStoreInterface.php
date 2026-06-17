<?php

namespace Modules\AgenticAI\Interfaces;

interface VectorStoreInterface
{
    /**
     * Search for similar documents.
     * 
     * @param string $queryText The user's question or text.
     * @param int $limit Number of results to return.
     * @return array Array of matching documents with scores.
     */
    public function search(string $queryText, int $limit = 5): array;

    /**
     * Store a document embedding.
     * 
     * @param string $id Unique ID (e.g., 'policy_123').
     * @param string $text The text content.
     * @param array $metadata Additional data (e.g., source, title).
     */
    public function upsert(string $id, string $text, array $metadata = []): void;
}
