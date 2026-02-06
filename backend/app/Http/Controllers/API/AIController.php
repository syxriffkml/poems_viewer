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
        $request->validate([
            'prompt' => 'required|string|min:3|max:500'
        ]);

        try {
            $poem = $this->groqService->generatePoem($request->prompt);

            return response()->json([
                'success' => true,
                'data' => [
                    'poem' => $poem,
                    'prompt' => $request->prompt
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
