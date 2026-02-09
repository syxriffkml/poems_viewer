<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\GroqService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class AIController extends Controller
{
    protected GroqService $groqService;

    public function __construct(GroqService $groqService)
    {
        $this->groqService = $groqService;
    }

    /**
     * Generate a poem from a prompt
     */
    public function generatePoem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prompt' => 'required|string|min:3|max:500',
            'style' => 'nullable|string|in:sonnet,haiku,free_verse,limerick,ballad,romantic,dark,nature',
            'length' => 'nullable|string|in:short,medium,long',
            'rhyme_scheme' => 'nullable|string|in:AABB,ABAB,ABCB,free',
            'tone' => 'nullable|string|in:joyful,melancholic,dramatic,peaceful,playful'
        ]);

        try {
            $result = $this->groqService->generatePoem(
                $validated['prompt'],
                $validated['style'] ?? null,
                $validated['length'] ?? null,
                $validated['rhyme_scheme'] ?? null,
                $validated['tone'] ?? null
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'title' => $result['title'],
                    'poem' => $result['poem'],
                    'prompt' => $validated['prompt']
                ],
                'message' => 'Poem generated successfully'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'GENERATION_ERROR',
                    'message' => 'Failed to generate poem. Please try again.',
                    'details' => config('app.debug') ? $e->getMessage() : null
                ]
            ], 500);
        }
    }

    /**
     * Categorize a poem's content
     */
    public function categorizePoem(Request $request): JsonResponse
    {
        $request->validate([
            'content' => 'required|string|min:10'
        ]);

        try {
            $analysis = $this->groqService->categorizePoem($request->content);

            return response()->json([
                'success' => true,
                'data' => $analysis,
                'message' => 'Poem analyzed successfully'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'ANALYSIS_ERROR',
                    'message' => 'Failed to analyze poem',
                    'details' => config('app.debug') ? $e->getMessage() : null
                ]
            ], 500);
        }
    }

    /**
     * Generate tags for a poem
     */
    public function generateTags(Request $request): JsonResponse
    {
        $request->validate([
            'content' => 'required|string|min:10'
        ]);

        try {
            $tags = $this->groqService->generateTags($request->content);

            return response()->json([
                'success' => true,
                'data' => [
                    'tags' => $tags
                ],
                'message' => 'Tags generated successfully'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'TAG_GENERATION_ERROR',
                    'message' => 'Failed to generate tags',
                    'details' => config('app.debug') ? $e->getMessage() : null
                ]
            ], 500);
        }
    }
}
