<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class GroqService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.groq.com/openai/v1';

    public function __construct()
    {
        $this->apiKey = config('services.groq.api_key');

        if (empty($this->apiKey)) {
            throw new Exception('Groq API key not configured');
        }
    }

    /**
     * Generate a poem based on a user prompt
     */
    public function generatePoem(string $prompt): array
    {
        try {
            $response = Http::withoutVerifying()->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($this->baseUrl . '/chat/completions', [
                'model' => 'llama-3.3-70b-versatile',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a skilled poet with expertise in various poetic forms and styles. Generate beautiful, creative poems based on user prompts. IMPORTANT: Your response must be in this exact format:
TITLE: [A short, evocative title for the poem]
---
[The poem content with proper line breaks and stanzas]

Be creative and evocative in your language. The title should be 2-6 words that capture the essence of the poem.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.8,
                'max_tokens' => 1024,
            ]);

            if (!$response->successful()) {
                Log::error('Groq API error', ['response' => $response->body()]);
                throw new Exception('Failed to generate poem: ' . $response->body());
            }

            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? '';

            // Parse title and poem
            $title = '';
            $poem = $content;

            // Try multiple parsing patterns
            if (preg_match('/TITLE:\s*(.+?)\s*---\s*(.+)/s', $content, $matches)) {
                // Format: TITLE: [title]\n---\n[poem]
                $title = trim($matches[1]);
                $poem = trim($matches[2]);
            } elseif (preg_match('/^(.+?)\s*---\s*(.+)/s', $content, $matches)) {
                // Format: [title]\n---\n[poem] (no TITLE: prefix)
                $potentialTitle = trim($matches[1]);
                // Only treat as title if it's short (less than 100 chars) and doesn't contain newlines
                if (strlen($potentialTitle) < 100 && strpos($potentialTitle, "\n") === false) {
                    $title = $potentialTitle;
                    $poem = trim($matches[2]);
                }
            } elseif (preg_match('/TITLE:\s*(.+?)\n\n(.+)/s', $content, $matches)) {
                // Format: TITLE: [title]\n\n[poem]
                $title = trim($matches[1]);
                $poem = trim($matches[2]);
            }

            return [
                'title' => $title ?: 'Untitled',
                'poem' => $poem
            ];

        } catch (Exception $e) {
            Log::error('Groq service error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Categorize a poem's content and determine sentiment
     */
    public function categorizePoem(string $poemContent): array
    {
        try {
            $response = Http::withoutVerifying()->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(20)->post($this->baseUrl . '/chat/completions', [
                'model' => 'llama-3.3-70b-versatile',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Analyze the poem and return ONLY a JSON object with two fields: "categories" (array of 2-4 categories) and "sentiment" (primary emotion). Available categories: romantic, melancholic, happy, sad, nostalgic, hopeful, dark, inspirational, nature, love, loss, life, death, reflective, celebratory. Format: {"categories": ["category1", "category2"], "sentiment": "happy"}'
                    ],
                    [
                        'role' => 'user',
                        'content' => "Analyze this poem:\n\n" . $poemContent
                    ]
                ],
                'temperature' => 0.3,
                'max_tokens' => 200,
            ]);

            if (!$response->successful()) {
                Log::error('Groq categorization error', ['response' => $response->body()]);
                return $this->getDefaultCategories();
            }

            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? '{}';

            // Extract JSON from the response
            $content = trim($content);
            if (str_starts_with($content, '```json')) {
                $content = trim(str_replace(['```json', '```'], '', $content));
            }

            $result = json_decode($content, true);

            if (!isset($result['categories']) || !isset($result['sentiment'])) {
                return $this->getDefaultCategories();
            }

            return [
                'categories' => array_slice($result['categories'], 0, 4),
                'sentiment' => $result['sentiment'],
                'tags' => $result['categories'] // Use categories as tags
            ];

        } catch (Exception $e) {
            Log::error('Groq categorization error', ['error' => $e->getMessage()]);
            return $this->getDefaultCategories();
        }
    }

    /**
     * Generate tags for a poem
     */
    public function generateTags(string $poemContent): array
    {
        try {
            $response = Http::withoutVerifying()->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(15)->post($this->baseUrl . '/chat/completions', [
                'model' => 'llama-3.3-70b-versatile',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Generate 3-6 relevant tags for this poem. Return ONLY a JSON array of strings. Example: ["spring", "renewal", "hope", "nature"]'
                    ],
                    [
                        'role' => 'user',
                        'content' => "Generate tags for this poem:\n\n" . $poemContent
                    ]
                ],
                'temperature' => 0.4,
                'max_tokens' => 100,
            ]);

            if (!$response->successful()) {
                return ['poetry', 'verse'];
            }

            $data = $response->json();
            $content = trim($data['choices'][0]['message']['content'] ?? '[]');

            // Extract JSON from the response
            if (str_starts_with($content, '```json')) {
                $content = trim(str_replace(['```json', '```'], '', $content));
            }

            $tags = json_decode($content, true);

            if (!is_array($tags)) {
                return ['poetry', 'verse'];
            }

            return array_slice($tags, 0, 6);

        } catch (Exception $e) {
            Log::error('Groq tags generation error', ['error' => $e->getMessage()]);
            return ['poetry', 'verse'];
        }
    }

    /**
     * Get default categories when AI fails
     */
    private function getDefaultCategories(): array
    {
        return [
            'categories' => ['general', 'poetry'],
            'sentiment' => 'reflective',
            'tags' => ['poetry', 'verse']
        ];
    }
}
