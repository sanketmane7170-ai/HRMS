<?php

namespace Modules\AgenticAI\Services\Ingestion;

class RecursiveChunker
{
    protected int $chunkSize;
    protected int $overlap;

    /**
     * @param int $chunkSize Target size in characters (approx)
     * @param int $overlap Character overlap to preserve context
     */
    public function __construct(int $chunkSize = 1000, int $overlap = 200)
    {
        $this->chunkSize = $chunkSize;
        $this->overlap = $overlap;
    }

    /**
     * Split text into chunks.
     * Uses simple recursive splitting by newlines, then spaces.
     */
    public function split(string $text): array
    {
        if (strlen($text) <= $this->chunkSize) {
            return [$text];
        }

        // Split by paragraphs first
        $parts = explode("\n\n", $text);
        $chunks = [];
        $currentChunk = "";

        foreach ($parts as $part) {
            // cleaning
            $part = trim($part);
            if (empty($part)) continue;

            // If adding this part exceeds chunk size, save current and start new
            if (strlen($currentChunk . "\n\n" . $part) > $this->chunkSize) {
                if (!empty($currentChunk)) {
                    $chunks[] = $currentChunk;
                    // Start new chunk with overlap (last N chars of previous)
                    // For simplicity, we just start fresh, or carry over last sentence if we were fancy.
                    // Overlap implementation:
                    $currentChunk = substr($currentChunk, -$this->overlap) . "\n\n" . $part;
                } else {
                    // Part itself is huge, need to split by sentences or just hard cut
                    // For now, hard cut if massive
                    $chunks[] = substr($part, 0, $this->chunkSize);
                    $currentChunk = substr($part, $this->chunkSize); 
                }
            } else {
                $currentChunk .= (empty($currentChunk) ? "" : "\n\n") . $part;
            }
        }

        if (!empty($currentChunk)) {
            $chunks[] = $currentChunk;
        }

        return $chunks;
    }
}
