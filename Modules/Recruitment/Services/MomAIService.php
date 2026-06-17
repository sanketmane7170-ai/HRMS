<?php

namespace Modules\Recruitment\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MomAIService
{
    protected $apiKey;
    protected $baseUrl;
    protected $model;

    public function __construct()
    {
        $this->apiKey = env('FLOWVERSAL_API_KEY');
        $this->baseUrl = env('FLOWVERSAL_BASE_URL');
        $this->model = env('FLOWVERSAL_MODEL', 'qwen2.5:14b');
    }

    /**
     * Generate specific job content field based on full context.
     *
     * @param string $targetField (description, requirements, responsibilities, skills)
     * @param array $context (title, skills, department, job_type, experience_level, etc.)
     * @return string|array|null
     */
    public function generateJobField(string $targetField, array $context)
    {
        // 1. Check if AI is enabled/configured
        if (!$this->apiKey || !$this->baseUrl) {
            Log::warning('MOM AI: Missing configuration');
            return null;
        }

        // 2. Construct the Prompt
        $prompt = "You are an expert HR Specialist. Write professional content for a job posting.\n\n";
        $prompt .= "CONTEXT:\n";
        $prompt .= "Job Title: " . ($context['title'] ?? 'N/A') . "\n";
        $prompt .= "Department: " . ($context['department'] ?? 'N/A') . "\n";
        $prompt .= "Experience: " . ($context['experience_level'] ?? 'N/A') . "\n";
        $prompt .= "Type: " . ($context['job_type'] ?? 'N/A') . "\n";
        $prompt .= "Location: " . ($context['location'] ?? 'N/A') . "\n";
        if (isset($context['remote_work'])) {
            $prompt .= "Remote: " . ($context['remote_work'] ? 'Yes' : 'No') . "\n";
        }
        if (!empty($context['skills'])) {
            $prompt .= "Skills Context: " . $context['skills'] . "\n";
        }
        
        $prompt .= "\nTASK: Generate ONLY the '" . strtoupper($targetField) . "' section.\n";

        if ($targetField === 'description') {
            $prompt .= "Output: A detailed professional summary and list of key duties. Use plain text with clear spacing. For lists, use '-' followed by a space. Do NOT use HTML or Markdown tags.";
        } elseif ($targetField === 'requirements') {
            $prompt .= "Output: A bulleted list of qualifications/requirements. Use plain text. For lists, use '-' followed by a space. Do NOT use HTML or Markdown tags.";
        } elseif ($targetField === 'responsibilities') {
             $prompt .= "Output: A bulleted list of responsibilities. Use plain text. For lists, use '-' followed by a space. Do NOT use HTML or Markdown tags.";
        } elseif ($targetField === 'skills') {
            $prompt .= "Output: A JSON array of short skill tags (e.g. [\"Python\", \"Django\"]). Return ONLY the JSON array.";
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(60)->post($this->baseUrl . '/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system', 
                        'content' => "You are a professional HR assistant. CRITICAL: Output ONLY the requested content. Start your response immediately with the content. Do NOT use HTML tags. Do NOT use Markdown formatting like **bold** or # headers."
                    ],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.1, 
                'max_tokens' => 1000,
                'stream' => false
            ]);

            if ($response->successful()) {
                $rawBody = $response->body();
                $content = '';

                // Try standard JSON first
                $data = $response->json();
                if (is_array($data) && isset($data['choices'][0]['message']['content'])) {
                    $content = $data['choices'][0]['message']['content'];
                } else {
                    // FALLBACK: Handle SSE Stream (data: {...})
                    $lines = explode("\n", $rawBody);
                    $bestContent = '';
                    foreach (array_reverse($lines) as $line) {
                        $line = trim($line);
                        if (empty($line) || $line === 'data: [DONE]') continue;
                        if (str_starts_with($line, 'data: ')) {
                            $chunk = json_decode(substr($line, 6), true);
                            if (isset($chunk['content']) && !empty($chunk['content'])) { $bestContent = $chunk['content']; break; }
                            if (isset($chunk['choices'][0]['delta']['content'])) { $bestContent = $chunk['choices'][0]['delta']['content'] . $bestContent; }
                        }
                    }
                    $content = $bestContent;
                }

                if (empty($content)) return null;

                // --- ROBUST CLEANUP ---
                
                // 1. Remove <LM...> tags and similar AI commentary blocks
                $content = preg_replace('/<LM.*?>/su', '', $content);
                
                // 2. Remove markdown code blocks
                $content = str_replace(['```json', '```html', '```'], '', $content);
                
                // 3. Safety: Strip ALL actual HTML tags to ensure clean textarea display
                if ($targetField !== 'skills') {
                    $content = strip_tags($content);
                    // Also clean up common AI "thought" artifacts or residue if extraction failed
                    $content = preg_replace('/^.*?:/s', '', $content, 1); // Remove "Description: " etc if AI repeated it
                }

                $content = trim($content);

                // Final processing for skills
                if ($targetField === 'skills') {
                    // Extract anything between [ and ]
                    if (preg_match('/\[.*\]/s', $content, $matches)) {
                        $content = $matches[0];
                    }
                    $decoded = json_decode($content, true);
                    return is_array($decoded) ? $decoded : [];
                }
                
                return $content;
            }
        } catch (\Exception $e) {
            Log::error('MOM AI: Exception', ['error' => $e->getMessage()]);
        }

        return null;
    }

}
